<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/places/tools.php");

class CPlaces extends CHtmlList
{
	function action()
	{
	}
	function init()
	{
		global $g;

		$this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(m.parent_comment_id) FROM groups_forum_comment_comment AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM groups_forum_comment_comment AS m 
			" . $this->m_sql_from_add . "
		";

        $this->m_field['comment_id'] = array("comment_id", null);
		$this->m_field['parent_comment_id'] = array("parent_comment_id", null);
		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['comment_text'] = array("comment_text", null);
		$this->m_field['created_at'] = array("created_at", null);

		$where = "";
		#$this->m_debug = "Y";
        
        $parent_comment_id = get_param('comment_id');
        if($parent_comment_id)
        {
            $where .= " AND parent_comment_id = " . to_sql($parent_comment_id, 'Number');
        }
		
		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "parent_comment_id";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        $this->m_field['user_id'][1] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
        if ($this->m_field['user_id'][1] == "") $this->m_field['user_id'][1] = "blank";
        
        $this->m_field['parent_comment_id'][1] = DB::result("SELECT comment_text FROM groups_forum_comment WHERE comment_id=" . $row['parent_comment_id'] . "", 0, 2);
        if ($this->m_field['parent_comment_id'][1] == "") $this->m_field['parent_comment_id'][1] = "blank";
        
        $this->m_field['comment_text'][1] = strcut($row['comment_text'], 48); 
        $this->m_field['parent_comment_id'][1] = strcut($this->m_field['parent_comment_id'][1], 48);
        
        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }
		
		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "groups_forum_comment_comments.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
