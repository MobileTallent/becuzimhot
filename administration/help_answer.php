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

	function action()
	{
		global $g;
        global $p;

		$cmd = get_param("cmd", "");
		$t = get_param("t", 1);
        $lang = get_param('lang', '');

		if ($cmd == "delete")
		{
			DB::execute("
				DELETE FROM help_answer WHERE
				id=" . to_sql(get_param("id", ""), "Number") . "
			");

			redirect("$p?t=$t&lang=$lang");
		}
		elseif ($cmd == "edit")
		{
			DB::execute("
				UPDATE help_answer
				SET
				name=" . to_sql(get_param("name", ""), "Text") . ",
				text=" . to_sql(get_param("text", ""), "Text") . "
				WHERE id=" . to_sql(get_param("id", ""), "Number") . "
			");
			redirect("$p?action=saved&t=$t&lang=$lang");
		}
		elseif ($cmd == "add")
		{
			DB::execute("
				INSERT INTO help_answer (topic_id, name, text)
				VALUES(
				" . to_sql($t, "Number") . ",
				" . to_sql(get_param("name", ""), "Text") . ",
				" . to_sql(get_param("text", ""), "Text") . ")
			");
			redirect("$p?t=$t&lang=$lang");
		}
	}

	function parseBlock(&$html)
	{
		global $g;

		$html->setvar("message", $this->message);

        $languageCurrent = get_param('lang', 'default');
        $html->setvar('lang', $languageCurrent);

		$t = get_param("t", 1);

		$topic_name = DB::result("SELECT name FROM help_topic WHERE id=" . to_sql($t, "Number") . "");
		$html->setvar("topic_name", $topic_name == "0" ? "" : $topic_name);
		$html->setvar("topic_id", $t);

		DB::query("SELECT * FROM help_answer
            WHERE topic_id=" . to_sql($t, "Number") . "
            ORDER BY id");
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

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "help_answer.html");

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
