<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");


class CPage extends CHtmlBlock
{
    public $comment = null;

    function init()
    {
        $pid = ipar('id');
        if ($pid > 0) {
            $this->comment = DB::row("SELECT * FROM gallery_comments WHERE id = ".  to_sql($pid,"Number")." LIMIT 1");
        }
        if (!isset($this->comment) or !is_array($this->comment)) {
            redirect('alb_comments.php');
        }
    }
	function action()
	{

    if(get_param("cmd")=="delete")
        {
            $pid = ipar('id');
            Gallery::commentDelete($pid,false,true);
            redirect("alb_comments.php");
        }
    if(get_param("cmd")=="update")
        {
            $pid = ipar('id');
            $data['comment'] = get_param('comment');
            DB::update('gallery_comments',$data,"`id` = ".  to_sql($pid,"Number") ."");
            redirect("alb_comments.php");
        }
	}
	function parseBlock(&$html)
	{
        $html->setvar('comment_name',$this->comment['name']);
        $html->setvar('comment_user_id',$this->comment['user_id']);
        $html->setvar('comment_id',$this->comment['id']);
        $html->setvar('comment',htmlspecialchars($this->comment['comment']));
    	parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "alb_comments_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
