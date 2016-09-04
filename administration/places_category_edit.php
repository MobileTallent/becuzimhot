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
	            "FROM places_category as m ".
	            "WHERE m.id=" . to_sql($category_id, 'Number') . " LIMIT 1");
	        if($category = DB::fetch_row())
	        {
                $title = get_param('category_title');
                DB::execute('UPDATE places_category SET ' . 
                    ' title=' . to_sql($title,"Text") .
					' WHERE id=' . $category_id);
					redirect("places_categories.php");
	        }
		redirect("places_categories.php");
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $category_id = get_param('category_id');
        DB::query("SELECT m.* ".
            "FROM places_category as m ".
            "WHERE m.id=" . to_sql($category_id, 'Number') . " LIMIT 1");
        if($category = DB::fetch_row())
        {
        	$html->setvar('category_id', $category['id']);
        	$html->setvar('category_title', htmlentities($category['title'],ENT_QUOTES,"UTF-8"));
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "places_category_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
