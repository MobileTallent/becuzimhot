<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$cmd = get_param('cmd', '');

$g_user = User::getInfoFull(get_param('id', ''));
if(!$g_user && $cmd != 'location') {
    redirect('users_results.php');
}

class CForm extends UserFields //CHtmlBlock
{
	var $message = "";
	var $login = "";
	function action()
	{
        global $g;
        global $g_user;
        global $p;

		$cmd = get_param('cmd', '');

            if ($cmd == 'update')
            {
                $this->message = "";
                $orientation = get_param('orientation', 1);

                $name = trim(get_param('username'));
                $this->message .= User::validateName($name);

                $password = trim(get_param('password'));
                $mail = get_param('email', '');

                $month  = (int) get_param('month', 1);
                $day    = (int) get_param('day', 1);
                $year   = (int) get_param('year', 1980);

                $country = get_param('country', '');
                $state   = get_param('state', '');
                $city    = get_param('city', '');

                $this->message .= User::validatePassword($password, 4, 100);
                $this->message .= User::validate('email,birthday,country');
                $this->verification('admin');

                if ($this->message == '')
                {
                    $optionsSet = Common::getOption('set', 'template_options');
                    $selectionFileds = 'join';
                    if ($optionsSet == 'urban') {
                        $this->updateLookingFor($g_user['user_id']);
                        $selectionFileds = 'update_admin_urban';
                    }
                    $this->updatePartner(get_param('id', ''));
                    $this->updateInfo(get_param('id', ''), $selectionFileds);

                    $h = zodiac($year . '-' . $month . '-' .  $day);

                    $setSql = '';

                    $goldDays = get_param('gold_days', 0);
                    $type = get_param('type', 'none');
                    if (($g_user['gold_days'] != $goldDays && $g_user['type'] == $type)
                        ||($g_user['gold_days'] == $goldDays && $g_user['type'] != $type)
                        ||($g_user['gold_days'] != $goldDays && $g_user['type'] != $type)){

                        if($g_user['gold_days'] != $goldDays){
                            $timeStamp=time()+3600;  //+60 minutes
                            $date=date('Y-m-d',$timeStamp);
                            $hour=intval(date('H',$timeStamp));

                            $setSql.=', payment_day='.to_sql($date).', payment_hour='.to_sql($hour).' ';
                        }
                        User::upgradeCouple($g_user['user_id'], $goldDays, $type);
                    }
                    if ($goldDays == 0){
                            $type = 'none';
                    }
                    if ($password != $g_user['password'] && Common::isOptionActive('md5')) {
                        $password = md5($password);
                    }

                    if ($optionsSet == 'urban') {
                        $setSql .= ', credits = ' . to_sql(get_param('credits'), 'Number');
                        if ($goldDays > 0) {
                            $type = 'membership';
                            $setSql .= ", sp_sending_messages_per_day = 0";
                        } else {
                            $setSql .= ", set_hide_my_presence = '2', set_do_not_show_me_visitors = '2'";
                        }
                    }
                    $setSql .= ', moderator_photo = ' . to_sql(get_param('moderator_photo'), 'Number') . ',
                                 moderator_texts = ' . to_sql(get_param('moderator_texts'), 'Number') . ',
                                 moderator_vids_video = ' . to_sql(get_param('moderator_vids_video'), 'Number') . ',
                                 type = ' . to_sql($type);

                    DB::execute("UPDATE user SET
                                        name = " . to_sql($name, 'Text') . ",
                                        password = " . to_sql($password, 'Text') . ",
					gold_days=" . to_sql($goldDays, "Number") . ",
                    type=" . to_sql($type, "Text") . ",
					mail=" . to_sql($mail, "Text") . ",
					country_id=" . to_sql($country, "Number") . ",
					state_id=" . to_sql($state, "Number") . ",
					city_id=" . to_sql($city, "Number") . ",
					country=" . to_sql(Common::getLocationTitle('country', $country), 'Text') . ",
					state=" . to_sql(Common::getLocationTitle('state', $state), 'Text') . ",
					city=" . to_sql(Common::getLocationTitle('city', $city), 'Text') . ",
					birth='" . $year . "-" . $month . "-" .  $day . "',
					horoscope='" . $h . "',
					relation='" . ((int) get_param("relation", $g_user["relation"]) . "'") . ",
					use_as_online = " . to_sql(get_param('use_as_online'), 'Number') .
                    $setSql . "
					WHERE user_id=" . $g_user['user_id'] . ";
				");
                    if($orientation != $g_user['orientation']) {
                        User::setOrientation($g_user['user_id'], $orientation);
                    }

		    redirect("$p?id=".get_param("id")."&action=saved");
		}
	} elseif ($cmd == "insert_photo") {

            $photo_name = get_param("photo_name", "");
			$description = get_param("description", "");

            $this->message = User::validatePhoto("photo_file");

            if ($this->message == "")
            {
                uploadphoto($g_user['user_id'], "", $photo_name, $description, 1, "../");
            }
    } elseif ($cmd == "delete_photo") {

            $photo_id = get_param("photo_id", 0);
            if ($photo_id == 0)
			{
				return;
			}

			deletephoto($g_user['user_id'], $photo_id);

			redirect("$p?id=".get_param("id")."&action=saved");
    } elseif($cmd == 'location') {
        $param  = get_param('param');
        $method = 'list' . get_param('method');
        echo Common::$method($param, -1);
        die();
    } elseif($cmd == 'add_spotlight') {
        $data = array('date_spotlight' => date('Y-m-d H:i:s'));
        User::update($data, get_param("id",0));
    	redirect("$p?id=".get_param("id")."&action=saved");
    } elseif($cmd == 'remove_spotlight') {
        $data = array('date_spotlight' => '');
        User::update($data, get_param("id",0));
    	redirect("$p?id=".get_param("id")."&action=saved");
    }

	}
	function parseBlock(&$html)
	{
            global $g;
            global $g_user;
            global $l;

            $html->setvar('message', $this->message);

            $checked = 'checked';



            $html->setvar('field_physical_datails', l('physical_datails'));
            if($g_user['moderator_photo']) {
                $html->setvar("moderator_photo", $checked);
            }
            if($g_user['moderator_texts']) {
                $html->setvar("moderator_texts", $checked);
            }
            if($g_user['moderator_vids_video']) {
                $html->setvar("moderator_vids_video", $checked);
            }
            $html->parse('moderator');
            $l = loadLanguageAdmin();

            //$g_user = User::getInfoFull(get_param("id", ""));

            $html->setvar("user_id", $g_user['user_id']);
            $html->setvar('username_length', $g['options']['username_length']);
            $html->setvar("gold_days", $g_user['gold_days']);
            $html->setvar("user_name", $g_user['name']);
            $html->setvar("password", $g_user['password']);
            $optionsSet = Common::getOption('set', 'template_options');
            if ($optionsSet == 'urban') {
                $html->parse('menu_im');
                $html->setvar('field_physical_datails', l('appearance'));
                $html->setvar('credits', $g_user['credits']);
                $html->parse('user_credits');
                if ($g_user['is_photo_public'] != 'N') {
                    $html->parse('menu_add_spotlight');
                }
                if($g_user['date_spotlight']>'0000-00-00 00:00:00'){
                    $html->parse('menu_remove_spotlight');
                }
            } else {
                $html->parse('menu_editblog');
                $html->parse('menu_im');
                $html->parse('menu_chat');
                $html->parse('menu_mail');
            }
        if($g_user['use_as_online']) {
            $html->setvar("use_as_online", $checked);
        }

		if (strstr($_SERVER['HTTP_HOST'], "abk") and !strstr($_SERVER['PHP_SELF'], "dev"))
            $html->setvar("mail", get_param("mail", 'disabled@ondemoadmin.cp'));
		else
            $html->setvar("mail", get_param("mail", $g_user['mail']));

        //$this->parseFieldsAll($html, 'admin');

		$num_photos = DB::result("SELECT COUNT(photo_id) FROM photo WHERE user_id=" . $g_user['user_id'] . ";");

		if ($num_photos < 4) {
			$html->parse("photo_upload", true);
		}

		$html->setvar("num_photos", $num_photos);

		DB::query("SELECT photo_id, user_id, photo_name, description, visible FROM photo WHERE user_id=" . $g_user['user_id'] . " ORDER BY photo_id;");

		for ($i = 1; $i <= $num_photos; $i++)
		{
			$html->setvar("numer", $i);

			if ($row = DB::fetch_row())
			{
				$html->setvar("photo_id", $row['photo_id']);
				$html->setvar("photo_name", $row['photo_name']);
				$html->setvar("description", nl2br($row['description']));

				$html->setvar("visible", $row['visible'] == "N" ? "(pending audit)" : "");

				if ($i == 1 or $i == 3) $html->parse("photo_odd", true);
				else $html->setblockvar("photo_odd", "");

				if ($i == 2) $html->parse("photo_even", true);
				else $html->setblockvar("photo_even", "");

                if($i % 4 == 0) {
                    $html->parse('photo_delimiter');
                } else {
                    $html->setblockvar('photo_delimiter', '');
                }

				$html->parse("photo_item", true);

				$html->parse("photo", false);
			}
		}
		$html->parse("photo_edit", true);
        if (!Common::isOptionActive('personal_settings')) {
            $html->parse('btn_update', false);
        }
        $html->setvar('paid_days_length', Common::getOption('paid_days_length'));
		//$html->setvar("name", $g_user['name']);

		parent::parseBlock($html);
	}
}

$page = new CForm('', $g['tmpl']['dir_tmpl_administration'] . 'users_edit.html', false, false, false, 'admin', get_param('id'));
$page->formatValue = 'entities';

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");