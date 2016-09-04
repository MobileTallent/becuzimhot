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
        $cmd = get_param('cmd');
        if($cmd == 'update') {
            $category = get_param('category');
            foreach ($category as $key => $item) {
                DB::execute('UPDATE `forum_forum` SET `sort_rank`=' . to_sql($key) . ' WHERE id=' . to_sql($item));
            }
        }
	}
	function init()
	{
		global $g;

		$this->m_on_page = 1000;
		$this->m_sql_count = "SELECT COUNT(m.id) FROM forum_forum AS m, forum_category AS cat" . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM forum_forum AS m,
                 forum_category AS cat
			" . $this->m_sql_from_add . "
		";

		$this->m_field['id'] = array("id", null);
		$this->m_field['category_id'] = array("category_id", null);
		$this->m_field['title'] = array("title", null);

		$where = "";
		#$this->m_debug = "Y";

        $category_id = get_param('category_id');
        if($category_id)
        {
            $where .= " AND category_id = " . to_sql($category_id, 'Number');
        }

		$this->m_sql_where = "1 AND cat.id = m.category_id" . $where;
		$this->m_sql_order = "cat.sort_rank, m.category_id, m.sort_rank";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
        $html->setvar('cat_id', get_param('category_id'));
        if (get_param('category_id') != '') {
            $html->parse('save_btn');
            $html->parse('cursor_move');
            $html->parse('sortable_js');
        }
		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        $this->m_field['category_id'][1] = DB::result("SELECT title FROM forum_category WHERE id=" . $row['category_id'] . "", 0, 2);
        if ($this->m_field['category_id'][1] == "") $this->m_field['category_id'][1] = "blank";

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "forum_forums.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
