<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");
if (Common::isMultisite()) {
    redirect('home.php');
}
class CHon extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
	}
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

		if (get_param('start') == 'Y') {
			$users = 0;
			$photos = 0;
			set_time_limit(0);
			if (get_param('truncate') == 'Y') {
				DB::execute('TRUNCATE TABLE user');
				DB::execute('TRUNCATE TABLE userinfo');
				DB::execute('TRUNCATE TABLE userpartner');
				DB::execute('TRUNCATE TABLE photo');
			}
			DB::query('SELECT * FROM ' . to_sql(get_param('user_table'), 'Plain') . '', 3);
			while ($p = DB::fetch_row(3)) {
				if(get_param('truncate') != 'Y' and DB::result('SELECT user_id FROM user WHERE user_id=' . $p['ID']) == 0) {
					$go = true;
				} elseif (get_param('truncate') == 'Y') {
					$go = true;
				} else {
					$go = false;
				}

				if ($go) {
					$u = array();
					$ui = array();
					$up = array();

					$u['user_id'] = $p['ID'];
					$u['name'] = $p['NickName'];
					$u['password'] = $p['Password'];
					$u['mail'] = $p['Email'];
					$u['set_email_mail'] = 2;
					$u['set_email_interest'] = 2;
					$u['zip'] = $p['zip'];
					$u['birth'] = $p['DateOfBirth'];

					if (isset($p['LookingAge'])) {
						$age_s = explode('-', $p['LookingAge']);
						if (count($age_s) == 2) list($u['p_age_from'], $u['p_age_to']) = $age_s;
					}

					if ($p['Sex'] == 'male' and $p['LookingFor'] == 'female') {
						$u['orientation'] = 1;
						$u['p_orientation'] = 2;
					}
					elseif ($p['Sex'] == 'female' and $p['LookingFor'] == 'male') {
						$u['orientation'] = 2;
						$u['p_orientation'] = 1;
					}
					elseif ($p['Sex'] == 'male' and $p['LookingFor'] == 'male') {
						$u['orientation'] = 3;
						$u['p_orientation'] = 3;
					}
					elseif ($p['Sex'] == 'female' and $p['LookingFor'] == 'female') {
						$u['orientation'] = 4;
						$u['p_orientation'] = 4;
					}
					elseif ($p['Sex'] == 'male') {
						$u['orientation'] = 1;
						$u['p_orientation'] = 2;
					}
					elseif ($p['Sex'] == 'female') {
						$u['orientation'] = 2;
						$u['p_orientation'] = 1;
					}

					if (isset($p['Relationship'])) {
						if ($p['Relationship'] == 'cas') $u['relation'] = 3;
						elseif ($p['Relationship'] == 'fri') $u['relation'] = 4;
						elseif ($p['Relationship'] == 'mar') $u['relation'] = 1;
						elseif ($p['Relationship'] == 'act') $u['relation'] = 2;
						else $u['relation'] = 1;
					} else {
						$u['relation'] = 1;
					}

					if (get_parent_class('addon') == 'Y') {
						if ($p['Height'] == '1') $ui['height'] = 1;
						elseif ($p['Height'] == '2') $ui['height'] = 12;
						elseif ($p['Height'] == '3') $ui['height'] = 16;
						elseif ($p['Height'] == '4') $ui['height'] = 20;
						elseif ($p['Height'] == '5') $ui['height'] = 24;
						elseif ($p['Height'] == '6') $ui['height'] = 28;
						elseif ($p['Height'] == '7') $ui['height'] = 30;
						else $ui['height'] = 0;

						if ($p['Religion'] == '1') $ui['religion'] = 2;
						elseif ($p['Religion'] == '2') $ui['religion'] = 1;
						elseif ($p['Religion'] == '3') $ui['religion'] = 3;
						elseif ($p['Religion'] == '4') $ui['religion'] = 5;
						elseif ($p['Religion'] == '5') $ui['religion'] = 6;
						elseif ($p['Religion'] == '6') $ui['religion'] = 23;
						elseif ($p['Religion'] == '7') $ui['religion'] = 7;
						elseif ($p['Religion'] == '8') $ui['religion'] = 8;
						elseif ($p['Religion'] == '9') $ui['religion'] = 10;
						elseif ($p['Religion'] == '10') $ui['religion'] = 23;
						elseif ($p['Religion'] == '11') $ui['religion'] = 23;
						elseif ($p['Religion'] == '12') $ui['religion'] = 11;
						elseif ($p['Religion'] == '13') $ui['religion'] = 12;
						elseif ($p['Religion'] == '14') $ui['religion'] = 13;
						elseif ($p['Religion'] == '15') $ui['religion'] = 14;
						elseif ($p['Religion'] == '16') $ui['religion'] = 23;
						elseif ($p['Religion'] == '17') $ui['religion'] = 23;
						elseif ($p['Religion'] == '18') $ui['religion'] = 19;
						elseif ($p['Religion'] == '19') $ui['religion'] = 23;
						elseif ($p['Religion'] == '20') $ui['religion'] = 20;
						elseif ($p['Religion'] == '21') $ui['religion'] = 23;
						elseif ($p['Religion'] == '22') $ui['religion'] = 23;
						elseif ($p['Religion'] == '23') $ui['religion'] = 21;
						elseif ($p['Religion'] == '24') $ui['religion'] = 6;
						elseif ($p['Religion'] == '25') $ui['religion'] = 23;
						elseif ($p['Religion'] == '26') $ui['religion'] = 23;
						else $ui['religion'] = 23;

						if ($p['BodyType'] == '1') $ui['body'] = 2;
						elseif ($p['BodyType'] == '2') $ui['body'] = 7;
						elseif ($p['BodyType'] == '3') $ui['body'] = 3;
						elseif ($p['BodyType'] == '4') $ui['body'] = 1;
						elseif ($p['BodyType'] == '5') $ui['body'] = 1;
						elseif ($p['BodyType'] == '6') $ui['body'] = 1;
						else $ui['body'] = 0;

						if ($p['Ethnicity'] == '1') $ui['ethnicity'] = 1;
						elseif ($p['Ethnicity'] == '2') $ui['ethnicity'] = 1;
						elseif ($p['Ethnicity'] == '3') $ui['ethnicity'] = 2;
						elseif ($p['Ethnicity'] == '4') $ui['ethnicity'] = 3;
						elseif ($p['Ethnicity'] == '5') $ui['ethnicity'] = 9;
						elseif ($p['Ethnicity'] == '6') $ui['ethnicity'] = 4;
						elseif ($p['Ethnicity'] == '7') $ui['ethnicity'] = 7;
						elseif ($p['Ethnicity'] == '8') $ui['ethnicity'] = 10;
						elseif ($p['Ethnicity'] == '9') $ui['ethnicity'] = 8;
						elseif ($p['Ethnicity'] == '10') $ui['ethnicity'] = 5;
						elseif ($p['Ethnicity'] == '11') $ui['ethnicity'] = 6;
						else $ui['ethnicity'] = 0;

						if ($p['MaritalStatus'] == '1') $ui['status'] = 6;
						elseif ($p['MaritalStatus'] == '2') $ui['status'] = 1;
						elseif ($p['MaritalStatus'] == '3') $ui['status'] = 4;
						elseif ($p['MaritalStatus'] == '4') $ui['status'] = 1;
						elseif ($p['MaritalStatus'] == '5') $ui['status'] = 3;
						elseif ($p['MaritalStatus'] == '6') $ui['status'] = 5;
						else $ui['status'] = 0;

						if ($p['Education'] == '1') $ui['education'] = 1;
						elseif ($p['Education'] == '2') $ui['education'] = 2;
						elseif ($p['Education'] == '3') $ui['education'] = 2;
						elseif ($p['Education'] == '4') $ui['education'] = 3;
						elseif ($p['Education'] == '5') $ui['education'] = 4;
						elseif ($p['Education'] == '6') $ui['education'] = 5;
						elseif ($p['Education'] == '7') $ui['education'] = 5;
						elseif ($p['Education'] == '8') $ui['education'] = 6;
						elseif ($p['Education'] == '9') $ui['education'] = 6;
						elseif ($p['Education'] == '10') $ui['education'] = 6;
						else $ui['education'] = 0;

						if ($p['Income'] == '1') $ui['income'] = 6;
						elseif ($p['Income'] == '2') $ui['income'] = 1;
						elseif ($p['Income'] == '3') $ui['income'] = 4;
						elseif ($p['Income'] == '4') $ui['income'] = 1;
						elseif ($p['Income'] == '5') $ui['income'] = 3;
						elseif ($p['Income'] == '6') $ui['income'] = 5;
						else $ui['income'] = 0;

						if ($p['Smoker'] == '1') $ui['smoking'] = 3;
						elseif ($p['Smoker'] == '2') $ui['smoking'] = 4;
						elseif ($p['Smoker'] == '3') $ui['smoking'] = 2;
						elseif ($p['Smoker'] == '4') $ui['smoking'] = 1;
						else $ui['smoking'] = 0;

						if ($p['Drinker'] == '1') $ui['drinking'] = 1;
						elseif ($p['Drinker'] == '2') $ui['drinking'] = 4;
						elseif ($p['Drinker'] == '3') $ui['drinking'] = 3;
						elseif ($p['Drinker'] == '4') $ui['drinking'] = 2;
						else $ui['drinking'] = 0;
					}

					if ($p['Sex'] == 'male') $u['gender'] = 'M';
					elseif ($p['Sex'] == 'female') $u['gender'] = 'F';

					if ($p['Picture'] == '1') $u['is_photo'] = 'Y';
					elseif ($p['Picture'] == '0') $u['is_photo'] = 'N';

					if ($p['Country'] == 'DE') $u['country_id'] = '81';
					else $u['country_id'] = DB::result('SELECT country_id FROM geo_country WHERE code=' .  to_sql($p['Country']) . '');
					$u['country'] = DB::result('SELECT country_title FROM geo_country WHERE country_id=' .  to_sql($u['country_id']) . '');
					DB::query('SELECT city_id, city_title, state_id FROM geo_city WHERE city_title=' . to_sql($p['City']) . ' AND country_id=' .  to_sql($u['country_id']) . '');
					if ($city = DB::fetch_row()) {
						$u['city_id'] = $city['city_id'];
						$u['city'] = $city['city_title'];
						$u['state_id'] = $city['state_id'];
						$u['state'] = DB::result('SELECT state_title FROM geo_state WHERE state_id=' .  to_sql($u['state_id']) . '');
					} else {
						$u['city'] = DB::result('SELECT city_title FROM geo_city WHERE city_id=' .  to_sql($p['City']) . '');
						$u['state'] = '-';
					}

					$ui['user_id'] = $p['ID'];
					$ui['headline'] = $p['Headline'];
					if (isset($p['DescriptionYou'])) {
						$ui['essay'] = $p['DescriptionMe'] . "\n\n" . $p['DescriptionYou'];
					} else {
						$ui['essay'] = $p['DescriptionMe'];
					}

					$up['user_id'] = $p['ID'];

					$sql_u = '';
					foreach ($u as $k => $v) $sql_u .= $k . '=' . to_sql($v) . ', ';
					$sql_u = substr($sql_u, 0, strlen($sql_u) - 2);
					DB::execute('INSERT INTO user SET ' . $sql_u . '');

					$sql_ui = '';
					foreach ($ui as $k => $v) $sql_ui .= $k . '=' . to_sql($v) . ', ';
					$sql_ui = substr($sql_ui, 0, strlen($sql_ui) - 2);
					DB::execute('INSERT INTO userinfo SET ' . $sql_ui . '');

					$sql_up = '';
					foreach ($up as $k => $v) $sql_up .= $k . '=' . to_sql($v) . ', ';
					$sql_up = substr($sql_up, 0, strlen($sql_up) - 2);
					DB::execute('INSERT INTO userpartner SET ' . $sql_up . '');

					$users++;
				}
			}
			DB::query('SELECT * FROM ' . to_sql(get_param('media_table'), 'Plain') . '', 3);
			while ($m = DB::fetch_row(3)) {
				$file = '../' . get_param('dir') . '/' . $m['med_prof_id'] . '/photo_' . $m['med_file'];
				if ($m['med_type'] == 'photo' and is_file($file)) {
					$f = array();
					$f['photo_id'] = $m['med_id'];
					$f['user_id'] = $m['med_prof_id'];
					$f['photo_name'] = $m['med_title'];
					$f['description'] = $m['med_title'];
					$f['visible'] = 'Y';

					$prefix = "../_files/photo/" . $f['user_id'] . "_" . $f['photo_id'] . "_";
					$im = new Image();
					if ($im->loadImage($file)) {
						$im->resizeWH($g['image']['big_x'], $g['image']['big_y'], false, $g['image']['logo'], $g['image']['logo_size']);
						$im->saveImage($prefix . "b.jpg", $g['image']['quality']);
					}
					if ($im->loadImage($file)) {
						$im->resizeCropped($g['image']['medium_x'], $g['image']['medium_y'], $g['image']['logo'], 0);
						$im->saveImage($prefix . "m.jpg", $g['image']['quality']);
					}
					if ($im->loadImage($file)) {
						$im->resizeCropped($g['image']['small_x'], $g['image']['small_y'], $g['image']['logo'], 0);
						$im->saveImage($prefix . "s.jpg", $g['image']['quality']);
					}
					if ($im->loadImage($file)) {
						$im->resizeCropped($g['image']['root_x'], $g['image']['root_y'], $g['image']['logo'], 0);
						$im->saveImage($prefix . "r.jpg", $g['image']['quality']);
					}

					$sql_f = '';
					foreach ($f as $k => $v) $sql_f .= $k . '=' . to_sql($v) . ', ';
					$sql_f = substr($sql_f, 0, strlen($sql_f) - 2);
					DB::execute('INSERT INTO photo SET ' . $sql_f . '');
					$photos++;
				}
			}
			$html->setvar('users', $users);
			$html->setvar('photos', $photos);

            $vars = array('users' => $users, 'photos' => $photos);
            $html->setvar('success_text', lSetVars('success_text', $vars));
			$html->parse('success', true);
		} else {
			$html->parse('form', true);
		}

		parent::parseBlock($html);
	}
}

$page = new CHon("", $g['tmpl']['dir_tmpl_administration'] . "import_dolphin.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
