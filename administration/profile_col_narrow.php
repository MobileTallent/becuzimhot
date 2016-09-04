<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CProfileColumnNarrow extends CHtmlBlock {

    function action() {
        global $p;

        $cmd = get_param('cmd');
        if($cmd == 'update') {
            $order = get_param('order');
            $status = get_param('order_status');
            foreach ($order as $key => $item) {
                if(empty($status[$item])) {
                    $stat = 'N';
                } else {
                    $stat = 'Y';
                }
               DB::execute("UPDATE `col_order` SET `position`=".to_sql($key).",`status`='".$stat."' WHERE name=".to_sql($item));
            }
            redirect($p. '?action=saved');
        }
    }

    function parseBlock(&$html) {
        $where = '';
        if (!Common::isOptionActive('photo_rating_enabled')) {
            $where = "AND `name` != 'rating_photos'";
        }
        DB::query("SELECT * FROM `col_order` WHERE `section` = 'narrow' {$where} ORDER BY position");
        while ($row = DB::fetch_row()) {
            $html->setvar('name_block', l($row['name']));
            $html->setvar('name_block_field', $row['name']);
            if ($row['status'] == 'Y')
                $html->setvar('checked', 'checked');
            else
                $html->setvar('checked', '');
            $html->parse('order_item');
        }
        parent::parseBlock($html);
    }

}

$page = new CProfileColumnNarrow("", $g['tmpl']['dir_tmpl_administration'] . "profile_col_narrow.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuOptions());

include("../_include/core/administration_close.php");