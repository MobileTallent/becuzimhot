<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");

class CHon extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
		$action = get_param('action');
		$id = get_param('id');
		$msg_id = get_param('msg_id');
		if($action=='delete' && !empty($id)){
			DB::execute("update blog_msg set count_comment=count_comment-1 where id=".to_sql($msg_id));
			DB::execute("delete from blog_comment where id=".to_sql($id));
		}
	}
	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;
		$user_id = get_param('user_id',0);
		$user_name=DB::result("select name from user where user_id=".to_sql($user_id));
		$html->setvar("user_name", $user_name);
		parent::parseBlock($html);
	}
}


class Cgroups extends CHtmlList
{
	function init()
	{
		parent::init();
		global $g;
		global $g_user;
		$user_id = get_param('user_id');
		$this->m_on_page = 10;	
		$this->m_sql_count = "select count(M.id)  from blogs_comment as M   join blogs_post as B on B.id=M.post_id";			
		$this->m_sql = "select M.*,B.subject from blogs_comment as M  join blogs_post as B on B.id=M.post_id	";
		$this->m_sql_where = " M.user_id = ".to_sql( $user_id);
		$this->m_sql_order = " dt desc ";	
		$this->m_params = "&user_id=".get_param('user_id');
		$this->m_field['id'] = array("id", null);
		$this->m_field['comment'] = array("comment", null);
		$this->m_field['dt'] = array("dt", null);
		$this->m_field['subject'] = array("subject", null);
		$this->m_field['msg_id'] = array("msg_id", null);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		$user_id = get_param('user_id');
		$html->setvar("id", $row['id']);
		$html->setvar("msg_id", $row['msg_id']);
		$html->setvar("user_id", $user_id);
		$html->setvar("subject", $row['subject']);
		$html->setvar("text", $row['comment']);
		$html->setvar("data", date('d-m-Y',time_mysql_dt2u($row['dt'])));
		//$html->setvar("folder", $row['fname']);
		//$html->setvar("cat_id","&cat_id=".get_param('cat_id'));
	}
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
		
		$user_id = get_param('user_id');
		$html->setvar("user_id", $user_id);		
		
		parent::parseBlock($html);
	}
}


$page = new CHon("", $g['tmpl']['dir_tmpl_administration'] . "look_message_bcmt.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$group_list = new Cgroups("mail_list", null);
$page->add($group_list);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
