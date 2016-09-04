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

		if ($cmd == "add")
		{
	        $category_title = trim(get_param('category_title'));
                DB::query("SELECT position FROM music_category ORDER BY position DESC LIMIT 1");
                $sql = DB::fetch_row();
            if($category_title) DB::execute('INSERT INTO music_category VALUES(NULL,'. to_sql($category_title,"Text").','.to_sql($sql['position']+1).')');
	         
		redirect("music_categories.php");
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "music_category_add.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");