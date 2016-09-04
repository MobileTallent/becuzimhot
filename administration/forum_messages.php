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

		$this->m_sql_count = "SELECT COUNT(m.id) FROM forum_message AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM forum_message AS m 
			" . $this->m_sql_from_add . "
		";

		$this->m_field['id'] = array("id", null);
		$this->m_field['topic_id'] = array("topic_id", null);
		$this->m_field['message'] = array("message", null);

		$where = "";
		#$this->m_debug = "Y";

        $topic_id = get_param('topic_id');
        if($topic_id)
        {
            $where .= " AND topic_id = " . to_sql($topic_id, 'Number');
        }
		
		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "id";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        $this->m_field['topic_id'][1] = DB::result("SELECT title FROM forum_topic WHERE id=" . $row['topic_id'] . "", 0, 2);
        if ($this->m_field['topic_id'][1] == "") $this->m_field['topic_id'][1] = "blank";
        
        $this->m_field['message'][1] = strcut($row['message'], 48);
		
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

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "forum_messages.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
