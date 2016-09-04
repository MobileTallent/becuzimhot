<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/vids/tools.php");

class Cvids extends CHtmlList
{
    var $translateForAdmin = true;

	function action()
	{
        $cmd = get_param('cmd');
        if($cmd == 'update') {
            $vids = get_param('vids');
            $vids_check = get_param_array('vids_check');
            foreach ($vids as $key => $item) {
                $check =(in_array($item, $vids_check)) ? 1 : 0;
                $check = ', `check` = ' . $check;
                $sql = 'UPDATE `vids_category`
                           SET `position` = ' . to_sql($key, 'Number') .
                           $check .
                       ' WHERE `category_id` = ' . to_sql($item, 'Number');
                DB::execute($sql);
            }
        } elseif ($cmd == 'delete') {
            $items = get_param('item');
            if ($items != '') {
                $items =  explode(',', $items);
                foreach ($items as $id) {
                    $where = 'FIND_IN_SET(' . to_sql($id, 'Number') . ', `cat`) != 0';
                    $vids = DB::select('vids_video', $where, '', '', array('id', 'cat'));
                    foreach ($vids as $video) {
                        $cat = explode(',', $video['cat']);
                        if (count($cat) == 1) {
                            CVidsTools::delVideoById($video['id'], true);
                        } else {
                            unset($cat[array_search($id, $cat)]);
                            $cat = implode(',', $cat);
                            DB::update('vids_video', array('cat' => $cat), '`id` = ' . to_sql($video['id'], 'Number'));
                        }
                    }
                    DB::execute("DELETE FROM `vids_category` WHERE `category_id` = " . to_sql($id, 'Number'));
                }
            }
        }
	}
	function init()
	{
		global $g;

		$this->m_on_page = 1000;

		$this->m_sql_count = "SELECT COUNT(m.category_id) FROM vids_category AS m " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT m.*
			FROM vids_category AS m
			" . $this->m_sql_from_add . "
		";
		$this->m_field['category_id'] = array("category_id", null);
        $this->m_field['category_title'] = array("category_title", null,'vids_category');

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
            if ($row['check']) {
                $html->setvar('vids_checked', 'checked');
            } else {
                $html->setvar('vids_checked', '');
            }
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

$page = new Cvids("main", $g['tmpl']['dir_tmpl_administration'] . "vids_categories.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
