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
		$cmd = get_param("cmd", "");
		$id = get_param("id", "");
		$title = trim(get_param("name", ""));
		$country_id = intval(trim(get_param("country_id", "")));

		if ($cmd == "delete")
		{
            $del = get_param('item', 0);
            if ($del != 0) {
                $country = explode(',', $del);
                foreach ($country as $id) {
                    DB::execute("
                        DELETE FROM geo_state WHERE
                        state_id=" . to_sql($id, "Number") . "
                    ");

                    DB::execute("
                        DELETE FROM geo_city WHERE
                        state_id=" . to_sql($id, "Number") . "
                    ");
                }
            }
            die('ok');
			//global $p;
			//redirect($p . "?country_id=$country_id");
		}
		elseif ($cmd == "edit" && $title)
		{
			DB::execute("
				UPDATE geo_state
				SET
				state_title=" . to_sql($title, "Text") . "
				WHERE state_id=" . to_sql($id, "Number") . "
			");
            die('ok');
			//global $p;
			//redirect($p . "?country_id=$country_id&action=saved");
		}
		elseif ($cmd == "add")
		{
			DB::execute("
				INSERT INTO geo_state (state_title, country_id)
				VALUES(
				" . to_sql($title, "Text") . ", $country_id)
			");
			global $p;
			redirect($p . "?country_id=$country_id");
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

		$country_id = get_param("country_id", $first_country_id);
		$html->setvar("country_id", $country_id);

		DB::query("SELECT * FROM geo_state
		WHERE country_id = " . to_sql($country_id, 'Number') . "
		ORDER BY state_title ASC");
        $count = 0;
		while ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v)
			{
				$html->setvar($k, htmlentities($v, ENT_QUOTES, "UTF-8"));
			}

			$html->parse("state", true);
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

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "users_fields_states.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");

?>
