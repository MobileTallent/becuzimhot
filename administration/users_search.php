<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;

        $tmplOptionSet = Common::getOption('set', 'template_options');

		$status = array("online" => "Online Members",
						"new" => "New Members",
						"birthday" => "Birthday",
						"all" => "All Members",
		);
		$html->setvar("status_options", h_options($status, "all"));

		$country = get_param("country", "");
		$state = get_param("state", "");

		$html->setvar("country_options", DB::db_options("SELECT country_id, country_title FROM geo_country;", get_param("country", "")));

		$state_options = DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id=" . to_sql($country, "Number") . ";", $state);
		$html->setvar("state_options", $state_options);

		$state = DB::result("SELECT state_id, state_title FROM geo_state WHERE country_id=" . to_sql($country, "Number") . " AND state_id=" . to_sql($state, "Number") . ";");

		if ($state != "" and $state != 0)
		{
			$city_options = DB::db_options("SELECT city_id, city_title FROM geo_city WHERE state_id=" . to_sql($state, "Number") . ";", get_param("city", ""));
			$html->setvar("city_options", $city_options);
		}

		$html->setvar("p_age_from_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], $g['options']['users_age']));
		$html->setvar("p_age_to_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], $g['options']['users_age_max']));

        $lang = loadLanguageAdmin();
        if ($tmplOptionSet == 'urban') {
            if (UserFields::isActive('i_am_here_to')) {
                $checks = get_checks_param("i_am_here_to");
                $this->parseChecks($html, "SELECT id, title FROM const_i_am_here_to", $checks, 2, 0, "i_am_here_to", "i_am_here_to", $lang);
            }
        } else {
            if (UserFields::isActive('relation')) {
                $checks = get_checks_param("p_relation");
                $this->parseChecks($html, "SELECT id, title FROM const_relation", $checks, 2, 0, "p_relation", "relationship", $lang);
            }
        }

        if (UserFields::isActive('orientation')) {
            $checks = get_checks_param("p_orientation");
            $this->parseChecks($html, 'SELECT id, title FROM const_orientation', $checks, 2, 0, 'p_orientation', 'orientation', $lang);
        }

        $html->setvar('r_to', date('Y-m-d'));
        $html->setvar('location', l('location', $lang));


		parent::parseBlock($html);
	}

    public function translation($field, $value, $lang = null)
    {

        static $tmpl = null;
        if(!$tmpl) {
            $tmpl = Common::getOption('name', 'template_options');
        }
        if ($lang === null ) {
            $lang = loadLanguageAdmin();
        }

        $keys = array($tmpl . '_' . $field . '_' . $value, $field . '_' . $value, $value);
        return lCascade($value, $keys, $lang);
    }

	function parseChecks(&$html, $sql, $mask, $num_columns = 1, $add = 0, $p = "", $field = null, $lang = null)
	{

		if (DB::query($sql))
		{
            if ($field === null ) {
                $field = $p;
            }
            if ($lang === null ) {
                $lang = loadLanguageAdmin();
            }

            $html->setvar('field_title', l($field, $lang));

			$i = 0;
			$total_checks = DB::num_rows();
			$in_column = ceil(($total_checks + $add) / $num_columns);


			if ($p == "") {
				$p = "check";
			}

			while ($row = DB::fetch_row())
			{
				$i++;

				$html->setvar("id", $row[0]);
                //echo $row['title'] . '<br>';
                $value = $this->translation($field, $row['title'], $lang);
				$html->setvar("title", $value);
				if ($mask & (1 << ($row[0] - 1))) {
					$html->setvar("checked", " checked");
				} else {
					$html->setvar("checked", "");
				}

				if ($i % $in_column == 0 and $i != 0 and ($i != $total_checks or $add > 0) and $num_columns != 1) {
					$html->parse($p . "_column", false);
				} else {
					$html->setblockvar($p . "_column", "");
				}
				$html->parse($p, true);
			}
			$html->parse($p . "s", true);
			$html->setblockvar($p, "");
			DB::free_result();
		}
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_search.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
