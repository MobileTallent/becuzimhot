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
		$cmd = get_param("cmd", "");
		
		if ($cmd == "delete")
		{
			DB::execute("
				DELETE FROM contact_partner WHERE
				id=" . to_sql(get_param("id", ""), "Number") . "
			");
		}
		if ($cmd == "answer")
		{
			send_mail(
				get_param("mail"),
				$g['main']['info_mail'],
				$g['main']['title'],
				get_param("answer", "")
			);
		}
	}
	
	function parseBlock(&$html)
	{
		global $g;
		
		$html->setvar("message", $this->message);
	
		DB::query("SELECT * FROM contact_partner ORDER BY id");
		while ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v)
			{
				$v = nl2br($v);
				$html->setvar($k, strip_tags($v, '<br>'));
			}
			
			$html->parse("question", true);
		}
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "contact_partner.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
