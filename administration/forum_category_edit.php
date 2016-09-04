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

		if ($cmd == "update")
		{
	        $id = get_param('id');
	        DB::query("SELECT m.* ".
	            "FROM forum_category as m ".
	            "WHERE m.id=" . to_sql($id, 'Number') . " LIMIT 1");
	        if($category = DB::fetch_row())
	        {
                $title = get_param('title');
                DB::execute('UPDATE forum_category SET ' . 
                    ' title=' . to_sql($title,"Text") .
					' WHERE id=' . $id);
	        }
		redirect("forum_categories.php?action=saved");
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $id = get_param('id');
        DB::query("SELECT m.* ".
            "FROM forum_category as m ".
            "WHERE m.id=" . to_sql($id, 'Number') . " LIMIT 1");
        if($category = DB::fetch_row())
        {
        	$html->setvar('id', $category['id']);
        	$html->setvar('title', htmlentities($category['title'],ENT_QUOTES,"UTF-8"));
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "forum_category_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");