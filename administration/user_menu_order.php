<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CMobileUserMenu extends CHtmlBlock {

    function action() {
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
            redirect();
        }
    }

    function parseBlock(&$html) {
        $where = '';
        if (!Common::isOptionActive('photo_rating_enabled')) {
            $where = "AND `name` != 'photo_rating'";
        }
        DB::query("SELECT * FROM `col_order` WHERE `section` = 'mobile_user_menu' {$where} ORDER BY position");
        $lang = loadLanguageAdminMobile();
        while ($row = DB::fetch_row()) {
            if ($row['name'] == '3d_city' && !Common::isModuleCityActive()) {
                continue;
            }
            $html->setvar('name_block', l('user_menu_' . $row['name'], $lang));
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

$page = new CMobileUserMenu("", $g['tmpl']['dir_tmpl_administration'] . "user_menu_order.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuOptions());

include("../_include/core/administration_close.php");