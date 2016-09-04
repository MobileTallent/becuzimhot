<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/forum.php");

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
            $category_id = get_param('category_id');
            $title = trim(get_param('title'));
            $description = trim(get_param('description'));
	        if($title) 
	        {
	           CForumForum::create_new($category_id, $title, $description);	
	        }
	         
		redirect("forum_forums.php");
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		
        $category_options = '';
        DB::query("SELECT * FROM forum_category ORDER BY id");
        $lang = loadLanguageAdmin();
        while($category = DB::fetch_row())
        {
            $category_options .= '<option value=' . $category['id'] . ' >';
            $category_options .= l($category['title'],$lang); 
            $category_options .= '</option>';           
        }
        $html->setvar("category_options", $category_options);
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "forum_forum_add.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");