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
	        $group_id = get_param('group_id');
	        DB::query("SELECT m.* ".
	            "FROM groups_group as m ".
	            "WHERE m.group_id=" . to_sql($group_id, 'Number') . " LIMIT 1");
	        if($group = DB::fetch_row())
	        {
	            $category_id = get_param('category');
	            $group_private = get_param('group_private');
                $group_title = get_param('group_title');
                $group_description = get_param('group_description');
                
                DB::execute('UPDATE groups_group SET ' . 
                    'category_id=' . to_sql($category_id) .
                    ', group_private=' . to_sql($group_private) .
                    ', group_title=' . to_sql($group_title) .
                    ', group_description=' . to_sql($group_description) .
                    ', updated_at=NOW() WHERE group_id=' . $group['group_id']);
                                
                redirect("groups_group_edit.php?action=saved&group_id=".$group['group_id']);
	        }
		
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $group_id = get_param('group_id');
        DB::query("SELECT m.* ".
            "FROM groups_group as m ".
            "WHERE m.group_id=" . to_sql($group_id, 'Number') . " LIMIT 1");
        if($group = DB::fetch_row())
        {
        	$html->setvar('group_id', $group['group_id']);
            $html->setvar('user_id', $group['user_id']);
        	$html->setvar('group_private', $group['group_private']);
        	$html->setvar('group_title', he($group['group_title']));
        	$html->setvar('group_description', $group['group_description']);

            $category_options = '';
            DB::query("SELECT * FROM groups_category ORDER BY category_id");
            $lang = loadLanguageAdmin();
            while($category = DB::fetch_row())
            {
                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $group['category_id']) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], $lang, 'groups_category'); 
                $category_options .= '</option>';           
            }
            $html->setvar("category_options", $category_options);

            $group_private_options = '<option value=0 ' . ((!$group['group_private']) ? 'selected="selected"' : '') . '>';
            $group_private_options .= l('public'); 
            $group_private_options .= '</option>';           
            $group_private_options .= '<option value=1 ' . (($group['group_private']) ? 'selected="selected"' : '') . '>';
            $group_private_options .= l('private'); 
            $group_private_options .= '</option>';           
            $html->setvar("group_private_options", $group_private_options);
        
            DB::query("SELECT * FROM groups_group_image WHERE group_id=" . $group['group_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "groups_group_images/" . $image['image_id'] . "_b.jpg");
                $html->setvar("image_id", $image['image_id']);
                $html->parse("image");
                
                $n_images++;
                
                $html->parse('photo');
            }
            
            $html->parse('photo_edit');
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "groups_group_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");