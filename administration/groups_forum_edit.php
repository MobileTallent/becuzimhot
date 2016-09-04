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
	        $forum_id = get_param('forum_id');
	        DB::query("SELECT m.* ".
	            "FROM groups_forum as m ".
	            "WHERE m.forum_id=" . to_sql($forum_id, 'Number') . " LIMIT 1");
	        if($forum = DB::fetch_row())
	        {
                $forum_title = get_param('forum_title');
                $forum_description = get_param('forum_description');
                
                DB::execute('UPDATE groups_forum SET ' . 
                    'forum_title=' . to_sql($forum_title) .
                    ', forum_description=' . to_sql($forum_description) .
                    ', updated_at=NOW() WHERE forum_id=' . $forum['forum_id']);
                                
				redirect("groups_forums.php?action=saved");
	        }
		redirect("groups_forums.php");
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $forum_id = get_param('forum_id');
        DB::query("SELECT m.* ".
            "FROM groups_forum as m ".
            "WHERE m.forum_id=" . to_sql($forum_id, 'Number') . " LIMIT 1");
        if($forum = DB::fetch_row())
        {
        	$html->setvar('forum_id', $forum['forum_id']);
            $html->setvar('user_id', $forum['user_id']);
        	$html->setvar('forum_title', he($forum['forum_title']));
        	$html->setvar('forum_description', $forum['forum_description']);

            $html->parse('photo_edit');
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "groups_forum_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");