<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";
	function action()
	{
        global $g;
        global $g_options;

		$texts = get_param_array("do");
        $data_texts = array();
        DB::query("SHOW COLUMNS FROM texts");
        while ($row = DB::fetch_row()){
            if(($row[0] != 'id') or ($row[0] != 'user_id')){
               $data_texts[$row[0]] = get_param_array($row[0]);
            }
        }
		$redirect = false;
		foreach ($texts as $k => $v)
		{
			if ($v == "add")
			{
				DB::query("SELECT * FROM texts WHERE id=" . ((int) $k) . "");
				if ($row = DB::fetch_row())
				{   

					$sql = "";
					foreach ($row as $k2 => $v2)
					{
						if (isset($g['user_var'][$k2]) and ($k2 != "id" and $k2 != "user_id" and !is_int($k2) and $g['user_var'][$k2]['status']=='active'))
						{
                            if (isset($data_texts[$k2][$k])){
								$sql .= " " . $k2 . "=" . to_sql($data_texts[$k2][$k], "Text") . ", ";
                            } else {
                                $sql .= " " . $k2 . "=" . to_sql($v2, "Text") . ", ";
                            }
						}
					}
					if ($sql != '') {
                        $sql = substr($sql, 0, (strlen($sql) - 2));
                        DB::execute("UPDATE userinfo SET " . $sql . " WHERE user_id=" . $row['user_id'] . "");
                    }
				}
				DB::execute("DELETE FROM texts WHERE id=" . ((int) $k) . "");

				$redirect = true;

			}
			elseif ($v == "del")
			{
				DB::execute("DELETE FROM texts WHERE id=" . ((int) $k) . "");

				$redirect = true;

			}
		}
		global $p;
		if($redirect) redirect($p."?action=saved");

	}
	function parseBlock(&$html)
	{
		global $g;
		$html->setvar("message", $this->message);

		$table = get_param("t", "tips");
		$html->setvar("table", $table);

		DB::query("SELECT * FROM texts ORDER BY id DESC LIMIT 20", 2);
		$num=DB::num_rows(2);
		while ($row = DB::fetch_row(2))
		{
            $html->setvar("id", $row['id']);
			$html->setvar("user_id", $row['user_id']);
			$html->setvar("user_name", DB::result('SELECT name FROM user WHERE user_id=' . $row['user_id'] . ''));

			foreach ($row as $k => $v)
			{
				if ($k != "id" and $k != "user_id" and !is_int($k) && !empty($v))
				{
					$html->setvar("field", $k);
					$html->setvar("field_title", ucfirst($k));
					$html->setvar("value", he($v));
					if (!isset($g['user_var'][$k][0]));
					elseif ($g['user_var'][$k][0] == "text")
					{
                        $html->setvar("name_input",$k);
						$html->setvar("field_title",$g['user_var'][$k][2]);
						$html->parse("text", true);
					}
					elseif ($g['user_var'][$k][0] == "textarea")
					{
                        $html->setvar("name_input",$k);
						$html->setvar("field_title",$g['user_var'][$k][2]);
						$html->parse("textarea", true);
					}
				}
			}

				$html->parse("texts", true);
				$html->setblockvar("text", "");
				$html->setblockvar("textarea", "");

		}

		if($num==0)
		{
			$html->parse("msg",true);
		}
		else
		{
			$html->parse("texts_yes",true);
		}

		parent::parseBlock($html);
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_text.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
