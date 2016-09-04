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
	        $category_id = get_param('category_id');
	        DB::query("SELECT m.* ".
	            "FROM groups_category as m ".
	            "WHERE m.category_id=" . to_sql($category_id, 'Number') . " LIMIT 1");
	        if($category = DB::fetch_row())
	        {
                $category_title = get_param('category_title');
                DB::execute('UPDATE groups_category SET ' . 
                    ' category_title=' . to_sql($category_title,"Text") .
					' WHERE category_id=' . $category_id);
					redirect("groups_categories.php?action=saved");
	        }
		redirect("groups_categories.php");
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $category_id = get_param('category_id');
        DB::query("SELECT m.* ".
            "FROM groups_category as m ".
            "WHERE m.category_id=" . to_sql($category_id, 'Number') . " LIMIT 1");
        if($category = DB::fetch_row())
        {
        	$html->setvar('category_id', $category['category_id']);
        	$html->setvar('category_title', htmlentities($category['category_title'],ENT_QUOTES,"UTF-8"));
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "groups_category_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");