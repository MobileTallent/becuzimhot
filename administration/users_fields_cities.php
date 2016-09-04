<?php
/* (C) ABK-Soft Ltd., 2004-2006
IMPORTANT: This is a commercial software product
and any kind of using it must agree to the ABK-Soft
Ltd. license agreement.
It can be found at http://abk-soft.com/license.doc
This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");



class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g_options;
        global $p;

		$cmd = get_param("cmd", "");
		$id = get_param("id", "");
		$title = trim(get_param("name", ""));
		$state_id = trim(get_param("state_id", ""));
		$country_id = trim(get_param("country_id", ""));
		$lat = trim(get_param("lat", "")) * IP::MULTIPLICATOR;
		$long = trim(get_param("long", "")) * IP::MULTIPLICATOR;

		if ($cmd == "delete")
		{
            $del = get_param('item', 0);
            if ($del != 0) {
                $country = explode(',', $del);
                foreach ($country as $id) {
                    DB::execute("
                        DELETE FROM geo_city WHERE
                        city_id=" . to_sql($id, "Number") . "
                    ");
                }
            }
            die('ok');
			//global $p;
			//redirect($p . "?state_id=$state_id");
		}
		elseif ($cmd == "edit" && $title)
		{
			DB::execute("
				UPDATE geo_city
				SET
				`city_title`=" . to_sql($title, "Text") . ",
				`lat`=" . to_sql($lat, "Text") . ",
				`long`=" . to_sql($long, "Text") . "
				WHERE city_id=" . to_sql($id, "Number") . "
			");
            die('ok');
			//global $p;
			//redirect($p."?state_id=$state_id&action=saved");
		}
		elseif ($cmd == "add")
		{
			DB::execute("
				INSERT INTO geo_city (country_id, city_title, state_id, `lat`, `long`)
				VALUES(
				" . to_sql($country_id, "Text") . ",
				" . to_sql($title, "Text") . ",
				" . to_sql($state_id, "Text") . ",
				" . to_sql($lat, "Text") . ",
				" . to_sql($long, "Text") . "
				)
			");
			redirect($p . "?state_id=$state_id");
		}

        if($cmd == 'default') {
            Config::update('options', 'city_default', intval(get_param('city_id')));
            redirect($p . "?state_id=$state_id");
        }
	}

	function parseBlock(&$html)
	{
		global $g_options;

        $lang = get_param('lang');
        $html->setvar('select_options_language', adminLangsSelect('main', $lang));

		$html->setvar("message", $this->message);

		$first_country_id = DB::result("SELECT country_id FROM geo_country
		ORDER BY country_title ASC LIMIT 1");

		// if save by state_id
		$state = intval(get_param('state_id', ''));
		if($state) {
			$first_country_id = DB::result("SELECT country_id FROM geo_state
			WHERE state_id = $state
			LIMIT 1");
		}

		$country_id = intval(get_param("country_id", $first_country_id));

		$html->setvar("country_id_real", $country_id);

		$first_state_id = DB::result("SELECT state_id FROM geo_state WHERE country_id = $country_id ORDER BY state_title ASC LIMIT 1");

		$state_id = get_param("state_id", $first_state_id);
		$html->setvar("state_id", $state_id);

        $cityDefault = Common::getOption('city_default');

		DB::query("SELECT * FROM geo_city WHERE state_id = " . to_sql($state_id, "Number") . " ORDER BY city_title ASC");
        $count = 0;
		while ($row = DB::fetch_row())
		{
            $row['lat'] = $row['lat'] / IP::MULTIPLICATOR;
            $row['long'] = $row['long'] / IP::MULTIPLICATOR;
			foreach ($row as $k => $v)
			{
				$html->setvar($k, htmlentities($v, ENT_QUOTES, "UTF-8"));
			}

            $html->subcond($row['city_id'] != $cityDefault, 'set_as_default_city');

			$html->parse("city", true);
            $count++;
		}
        if ($count > 0) {
            $html->parse('delete_btn');
        }
        if ($count > 1) {
            $html->parse('select_btn');
        }

		$country_options = DB::db_options("SELECT country_id, country_title
		FROM geo_country
		ORDER BY country_title ASC", $country_id);
		$html->setvar("geo_countries", $country_options);

		$state_options = DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id = $country_id ORDER BY state_title ASC", $state_id);
		$html->setvar("geo_states", $state_options);

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "users_fields_cities.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");

?>
