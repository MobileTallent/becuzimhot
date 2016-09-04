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
	            "FROM forum_forum as m ".
	            "WHERE m.id=" . to_sql($id, 'Number') . " LIMIT 1");
	        if($forum = DB::fetch_row())
	        {
                $category_id = get_param('category_id');
	        	$title = get_param('title');
                $description = get_param('description');
                DB::execute('UPDATE forum_forum SET ' . 
                    ' category_id=' . to_sql($category_id,"Number") .
                    ', title=' . to_sql($title,"Text") .
                    ', description=' . to_sql($description,"Text") .
					' WHERE id=' . $id);
	        }
		redirect("forum_forums.php?action=saved");
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $id = get_param('id');
        DB::query("SELECT m.* ".
            "FROM forum_forum as m ".
            "WHERE m.id=" . to_sql($id, 'Number') . " LIMIT 1");
        if($forum = DB::fetch_row())
        {
        	$html->setvar('id', $forum['id']);
        	$html->setvar('title', htmlentities($forum['title'],ENT_QUOTES,"UTF-8"));
        	$html->setvar('description', htmlentities($forum['description'],ENT_QUOTES,"UTF-8"));
        	
            $category_options = '';
            DB::query("SELECT * FROM forum_category ORDER BY id");
            $lang = loadLanguageAdmin();
            while($category = DB::fetch_row())
            {
                $category_options .= '<option value=' . $category['id'] . ' ' . (($category['id'] == $forum['category_id']) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['title'],$lang); 
                $category_options .= '</option>';           
            }
            $html->setvar("category_options", $category_options);
        	
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "forum_forum_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");