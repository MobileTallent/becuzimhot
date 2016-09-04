<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminCityRooms extends CHtmlBlock {

    function action() {
        global $p;

        $cmd = get_param('cmd');
        if($cmd == 'update') {
            $order = get_param('order');
            $status = get_param('order_status');
            foreach ($order as $key => $item) {
                $stat = isset($status[$item])?1:0;
                if (!$stat) {
                    DB::delete('city_users', 'location = ' . to_sql($item));
                }
                $sql = "UPDATE `city_rooms`
                           SET `position` = " . to_sql($key) . ",
                               `status` = " .to_sql($stat) . "
                         WHERE `id` = " . to_sql($item);
                DB::execute($sql);
            }
            redirect($p . '?action=saved');
        }
    }

    function parseBlock(&$html) {
        DB::query("SELECT * FROM `city_rooms` ORDER BY position");
        while ($row = DB::fetch_row()) {
            $html->setvar('name_block', l($row['name']));
            $html->setvar('id_block', $row['id']);
            if ($row['status'])
                $html->setvar('checked', 'checked');
            else
                $html->setvar('checked', '');
            $html->parse('order_item');
        }
        parent::parseBlock($html);
    }

}

$page = new CAdminCityRooms("", $g['tmpl']['dir_tmpl_administration'] . "city_rooms.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuCity());

include("../_include/core/administration_close.php");