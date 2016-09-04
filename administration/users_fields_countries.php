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
	function action()
	{
		global $g_options;
        global $p;

		$cmd = get_param("cmd", "");
		$id = get_param("id", "");
		$title = trim(get_param("name", ""));

		if ($cmd == "delete")
		{
            $del = get_param('item', 0);
            if ($del != 0) {
                $country = explode(',', $del);
                foreach ($country as $id) {
                    DB::execute("DELETE FROM geo_country WHERE
                                 country_id=" . to_sql($id, "Number") . "
                    ");

                    DB::execute("DELETE FROM geo_state WHERE
                                 country_id=" . to_sql($id, "Number") . "
                    ");

                    DB::execute("DELETE FROM geo_city WHERE
                                 country_id=" . to_sql($id, "Number") . "
                    ");
                }
            }

			//global $p;
			//redirect($p);
            die('ok');
		}elseif ($cmd == "set_first"){
            $first = array_reverse(get_param_array('first_countries'));
            DB::update('geo_country', array('first' => 0));
            if (!empty($first)) {
                $i = 1;
                foreach ($first as $id) {
                    if ($id != 0) {
                        DB::update('geo_country', array('first' => $i), '`country_id` = ' .to_sql($id));
                        $i++;
                    }
                }
                redirect($p . '?action=saved');
            }
        }elseif ($cmd == "edit" && $title){
			DB::execute("
				UPDATE geo_country
				SET
				country_title=" . to_sql($title, "Text") . "
				WHERE country_id=" . to_sql($id, "Number") . "
			");
			//global $p;
			//redirect($p."?action=saved");
            die('ok');
		}
		elseif ($cmd == "add")
		{
			DB::execute("
				INSERT INTO geo_country (country_title)
				VALUES(
				" . to_sql($title, "Text") . ")
			");
			redirect($p . '?action=saved');
		}
	}

	function parseBlock(&$html)
	{
		global $g_options;

        $lang = get_param('lang');
        $html->setvar('select_options_language', adminLangsSelect('main', $lang));

		DB::query("SELECT * FROM geo_country ORDER BY country_title ASC");
        $count = 0;
		while ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v)
			{
				$html->setvar($k, htmlentities($v, ENT_QUOTES, "UTF-8"));
			}
			$html->parse("country", true);
            $count++;
		}

        $firstCountries = DB::select('geo_country', 'first != 0', 'first DESC, country_title ASC');
        $block = 'first_countries';
        $isFirst = false;
		foreach ($firstCountries as $key => $row) {
            $html->setvar($block . '_options', DB::db_options('SELECT * FROM geo_country ORDER BY country_title ASC', $row['country_id']));
            $html->parse($block . '_item');
            $isFirst = true;
        }
        if (!$isFirst) {
            $html->setvar($block . '_options', DB::db_options('SELECT * FROM geo_country ORDER BY country_title ASC'));
            $html->parse($block . '_item');
        }
        if ($count > 0) {
            $html->parse('delete_btn');
        }
        if ($count > 1) {
            $html->parse('select_btn');
        }

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "users_fields_countries.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");