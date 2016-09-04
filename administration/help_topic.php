<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{

	function action()
	{
		global $g_options;
		$cmd = get_param("cmd", "");
		$lang = get_param('lang', '');

		if ($cmd == "delete")
		{
			DB::execute("
				DELETE FROM help_topic WHERE
				id=" . to_sql(get_param("id", ""), "Number") . "
			");
			global $p;
			redirect($p . '?lang=' . $lang);
		}
		elseif ($cmd == "edit")
		{
			DB::execute("
				UPDATE help_topic
				SET
				name=" . to_sql(get_param("name", ""), "Text") . "
				WHERE id=" . to_sql(get_param("id", ""), "Number") . "
			");
			global $p;
			redirect("$p?action=saved&lang=$lang");
		}
		elseif ($cmd == "add")
		{
			DB::execute("
				INSERT INTO help_topic (name, lang)
				VALUES(
				" . to_sql(get_param("name", ""), "Text") . ", " . to_sql($lang) . ")
			");
			global $p;
			redirect($p . '?lang=' . $lang);
		}
	}

	function parseBlock(&$html)
	{
        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        $sql = 'SELECT * FROM help_topic
            WHERE lang = ' . to_sql($languageCurrent) . '
            ORDER BY id ASC';
		DB::query($sql);
		while ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v)
			{
				$html->setvar($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
			}

			$html->parse("question", true);
		}

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "help_topic.html");

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
