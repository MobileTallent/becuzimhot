<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/music/tools.php");

class CMusic extends CHtmlList
{
    var $translateForAdmin = true;
    
	function action()
	{
        $cmd = get_param('cmd');
        if ($cmd == 'update') {
            $music = get_param('music');
            foreach ($music as $key => $item) {
                DB::execute("UPDATE `music_category` SET `position`=" . to_sql($key) . " WHERE category_id=" . to_sql($item));
            }
        } elseif ($cmd == 'delete') {
            $items = get_param('item');
            if ($items != '') {
                $items =  explode(',', $items);
                foreach ($items as $id) {
                    $rows = DB::select('music_musician', '`category_id` = ' . to_sql($id, 'Number'), '', '', array('musician_id'));
                    foreach ($rows as $row) {
                        CMusicTools::delete_musician($row['musician_id'], true);
                    }
                    DB::execute("DELETE FROM `music_category` WHERE `category_id` = " . to_sql($id, 'Number'));
                }
            }
        }
	}
	function init()
	{
		global $g;

		$this->m_on_page = 1000;

		$this->m_sql_count = "SELECT COUNT(m.category_id) FROM music_category AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM music_category AS m
			" . $this->m_sql_from_add . "
		";
		$this->m_field['category_id'] = array("category_id", null);
        $this->m_field['category_title'] = array("category_title", null,'music_category');

		$where = "";
		#$this->m_debug = "Y";

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "position";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;


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

$page = new CMusic("main", $g['tmpl']['dir_tmpl_administration'] . "music_categories.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
