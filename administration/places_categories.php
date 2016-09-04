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
    var $translateForAdmin = true;
    
	function action()
	{
        $cmd = get_param('cmd');
        if ($cmd == 'update') {
            $places = get_param('places');
            //$status = get_param('status');
            foreach ($places as $key => $item) {
                //$statusItem = (isset($status[$item]) ) ? 1 : 0;
                DB::execute("UPDATE `places_category` SET `position`=" . to_sql($key) . " WHERE id=" . to_sql($item));
            }
        } elseif ($cmd == 'delete') {
            $items = get_param('item');
            if ($items != '') {
                $items =  explode(',', $items);
                foreach ($items as $id) {
                    $rows = DB::select('places_place', '`category_id` = ' . to_sql($id, 'Number'), '', '', array('id'));
                    foreach ($rows as $row) {
                        CPlacesTools::delete_place($row['id'], true);
                    }
                    DB::execute('DELETE FROM `places_category` WHERE `id` = ' . to_sql($id, 'Number'));
                }
            }
        }
	}
	function init()
	{
		global $g;

		$this->m_on_page = 1000;

		$this->m_sql_count = "SELECT COUNT(m.id) FROM places_category AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM places_category AS m
			" . $this->m_sql_from_add . "
		";
		$this->m_field['id'] = array("id", null);
        $this->m_field['title'] = array("title", null,'places_category');
        $this->m_field['title'] = array("title", null,'places_category');

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

$page = new CPlaces("main", $g['tmpl']['dir_tmpl_administration'] . "places_categories.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
