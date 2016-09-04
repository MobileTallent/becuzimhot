<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CUsersResults extends CHtmlList
{
	function action()
	{
		global $g, $p;
		$del = get_param('delete', 0);
        $approval = get_param('approval', 0);
        $view = get_param('view');
        $redirect = '';
        if ($view) {
            $redirect = "?view={$view}";
        }
		if ($del != 0)
		{
            $user =  explode(',', $del);
            foreach ($user as $userId) {
                if (Common::isEnabledAutoMail('admin_delete')) {
                    DB::query('SELECT * FROM user WHERE user_id = ' . to_sql($userId, 'Number'));
                    $row = DB::fetch_row();
                    $vars = array(
                        'title' => $g['main']['title'],
                    );
                    Common::sendAutomail($row['lang'], $row['mail'], 'admin_delete', $vars);
                }
                delete_user($userId);
            }

			redirect("{$p}{$redirect}");
		} elseif ($approval) {
            if ($approval == 'all') {
                $where = $view == 'activate' ? "active_code != ''" : "`active` = 0";
                $user =  DB::field('user', 'user_id', $where);
            } else {
                $user =  explode(',', $approval);
            }
            if (is_array($user)) {
                foreach ($user as $userId) {
                    $data = array('active' => 1, 'hide_time' => 0);
                    if ($view == 'activate') {
                        $data = array('active_code' => '');
                    }
                    DB::update('user', $data, '`user_id` = ' . to_sql($userId, 'Number'));
                    if ($view != 'activate' && Common::isEnabledAutoMail('profile_approved')) {
                        DB::query('SELECT * FROM `user` WHERE `user_id` = ' . to_sql($userId, 'Number'));
                        $row = DB::fetch_row();
                        $vars = array('title' => $g['main']['title'],
                                      'name' => $row['name'],
                                      'password' => $row['password'],
                        );
                        Common::sendAutomail($row['lang'], $row['mail'], 'profile_approved', $vars);
                    }
                }
            }
			redirect("{$p}{$redirect}");
        }
	}
	function init()
	{
		global $g;

        $this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(u.user_id) FROM user AS u " . $this->m_sql_from_add;
		$this->m_sql = "
			SELECT u.user_id, u.mail, u.type, u.orientation, u.password, u.gold_days, u.name, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
            ) AS age, u.last_visit,
			u.is_photo,
			u.city_id, u.state_id, u.country_id, u.last_ip, u.register
			FROM user AS u
			" . $this->m_sql_from_add;

		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['name'] = array("name", null);
		$this->m_field['age'] = array("age", null);
		$this->m_field['last_visit'] = array("last_visit", null);
		$this->m_field['city_title'] = array("city", null);
		$this->m_field['state_title'] = array("state", null);
		$this->m_field['country_title'] = array("country", null);
		$this->m_field['mail'] = array("mail", null);
		$this->m_field['type'] = array("type", null);
		$this->m_field['gold_days'] = array("gold_days", null);
		$this->m_field['password'] = array("password", null);
		$this->m_field['orientation'] = array("orientation", null);
		$this->m_field['last_ip'] = array("last_ip", null);
		$this->m_field['register'] = array("register", null);

        $view = get_param('view');
        if ($view == 'activate') {
            $where = " AND active_code != ''";
        } else {
            $where = " AND `active` = 0";
        }

		#$this->m_debug = "Y";
		$user["p_orientation"] = (int) get_checks_param("p_orientation");
		if ($user["p_orientation"] != "0")
		{
			$where .= " AND " . $user["p_orientation"] . " & (1 << (cast(orientation AS signed) - 1))";
		}

		$user["p_relation"] = (int) get_checks_param("p_relation");
		if ($user["p_relation"] != "0")
		{
			$where .= " AND " . $user["p_relation"] . " & (1 << (cast(relation AS signed) - 1))";
		}

		$user['name'] = get_param("name", "");
		if ($user['name'] != "")
		{
			$where .= " AND name LIKE '%" . to_sql($user['name'], "Plain") . "%'";
		}

		$user['mail'] = get_param("mail", "");
		if ($user['mail'] != "")
		{
			$where .= " AND mail LIKE '%" . to_sql($user['mail'], "Plain") . "%'";
		}

		if (get_param("gold", "") == "1")
		{
			$where .= " AND gold_days>0";
		}
		if (get_param("gold", "") == "0")
		{
			$where .= " AND gold_days=0";
		}

		$r_from = get_param("r_from", "0000-00-00");
		$r_to = get_param("r_to", "0000-00-00");
		if ($r_from != "0000-00-00" or $r_to != "0000-00-00")
		{
			#$from = explode("-", $r_from);
			#$to = explode("-", $r_to);
			#$r_from = mktime(0, 0, 0, $from[1], $from[2], $from[0] == "0000" ? "2000" : $from[0]);
			#$r_to = mktime(0, 0, 0, $to[1], $to[2], $to[0] == "0000" ? "2000" : $to[0]);
			#if ($r_from < $r_to)
			{
				$where .= " AND register>" . to_sql($r_from) . " AND register<" . to_sql($r_to) . "";
			}
		}

		$user['p_age_from'] = (int) get_param("p_age_from", 0);
		$user['p_age_to'] = (int) get_param("p_age_to", 0);
        if ($user['p_age_to'] == $g['options']['users_age_max']) $user['p_age_to'] = 10000;

		if ($user['p_age_from'] != 0)
		{
			$where .= " AND (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) >= " . $user['p_age_from'] . ") ";
		}

		if ($user['p_age_to'] != 0)
		{
			$where .= " AND (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
 <= " . $user['p_age_to'] . ") ";
		}



		$user['country'] = (int) get_param("country", 0);
		if ($user['country'] != 0 and $user['country'] != "")
		{
			$where .= " AND u.country_id=" . $user['country'] . "";
		}
		$user['state'] = (int) get_param("state", 0);
		if ($user['state'] != 0 and $user['state'] != "")
		{
			$where .= " AND u.state_id=" . $user['state'] . "";
		}
		$user['city'] = (int) get_param("city", 0);
		if ($user['city'] != 0 and $user['city'] != "")
		{
			$where .= " AND u.city_id=" . $user['city'] . "";
		}

		if (get_param("photo", "") == "1")
		{
			$where .= " AND u.is_photo='Y'";
		}

		if (get_param("status", "") == "online")
		{
			$where .= " AND last_visit>" . (time() - $g['options']['online_time'] * 60) . "";
		}
		elseif (get_param("status", "") == "new")
		{
			$where .= " AND register>" . (time() - $g['options']['new_days'] * 3600 * 24) . "";
		}
		elseif (get_param("status", "") == "birthday")
		{
			$where .= " AND (DAYOFMONTH(birth)=DAYOFMONTH('" . date('Y-m-d H:i:s') . "') AND MONTH(birth)=MONTH('" . date('Y-m-d H:i:s') . "'))";
		}

		$keyword = get_param("keyword", "");
		if ($keyword != "")
		{
			$keyword = to_sql($keyword, "Plain");
			$where .= " AND (name LIKE '%" . $keyword . "%') ";
		}

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "user_id";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
        $view = get_param('view');
        $type = 'approval';
        if ($view == 'activate') {
            $view = "&view={$view}";
            $type = 'activate';
        }
        $html->setvar('view', $view);
        $html->parse("menu_active_{$type}", false);
        $html->setvar('title_current', l("title_current_{$type}"));
        $html->setvar('action_type', l($type));
        $html->setvar('action_type_all', l("{$type}_all"));

		parent::parseBlock($html);
	}
    function onPostParse(&$html)
	{
        if ($this->m_total != 0) {
            $html->parse('no_delete');
            $html->parse('approval');
        }
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

		$this->m_field['city_title'][1] = DB::result("SELECT city_title FROM geo_city WHERE city_id=" . $row['city_id'] . "", 0, 2);
		if ($this->m_field['city_title'][1] == "") $this->m_field['city_title'][1] = "blank";
		$this->m_field['state_title'][1] = DB::result("SELECT state_title FROM geo_state WHERE state_id=" . $row['state_id'] . "", 0, 2);
		if ($this->m_field['state_title'][1] == "") $this->m_field['state_title'][1] = "blank";
		$this->m_field['country_title'][1] = DB::result("SELECT country_title FROM geo_country WHERE country_id=" . $row['country_id'] . "", 0, 2);
		if ($this->m_field['country_title'][1] == "") $this->m_field['country_title'][1] = "blank";

		if (((time() - $row['last_visit']) / 60) < $g['options']['online_time'])
		{
			$this->m_field['last_visit'][1] = "Online Now!";
		}

		$this->m_field['orientation'][1] = DB::result("SELECT title FROM const_orientation WHERE id=" . $row['orientation'] . "", 0, 2);
		if ($this->m_field['orientation'][1] == "")
		{
			$this->m_field['orientation'][1] = "Invilid orientation";
		}
        $this->m_field['password'][1] = hard_trim($row['password'], 7);
		if (strstr($_SERVER['HTTP_HOST'], "abk") and !strstr($_SERVER['PHP_SELF'], "dev")) {
			$this->m_field['mail'][1] = 'disabled@ondemoadmin.cp';
			$this->m_field['password'][1] = 'not shown in the demo';
		}

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CUsersResults("main", $g['tmpl']['dir_tmpl_administration'] . "users_approval.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>

