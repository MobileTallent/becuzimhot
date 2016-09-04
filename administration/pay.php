<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminPay extends CHtmlBlock {

    function action()
    {
        global $p;
        $cmd = get_param('cmd', '');

        if ($cmd == 'update') {
            $module = get_param('module');
            $options = get_param_array('option');
            Config::updateAll($module, $options);
            redirect($p . '?action=saved');
        }
    }

    function parseBlock(&$html)
    {
        global $g;

        $paymentModules = $g['payment_modules'];

        foreach ($paymentModules as $paymentModule => $values) {

            $config = Config::getOptionsAll($paymentModule, 'position', 'ASC', true);
            $html->setvar('module', $paymentModule);
            $html->setvar('payment', l($paymentModule));

            foreach ($config as $key => $row) {
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }

                $html->setvar('label', l(ucfirst(str_replace("_", " ", $key))));
                $field = $row['type'];

                if ($field == 'checkbox') {
                    $checked = '';
                    if ($row['value'] == 1 || $row['value'] == 'Y') {
                        $checked = 'checked';
                    }
                    $html->setvar('checked', $checked);
                }

                $html->parse('item_' . $field, false);

                $html->parse('item');
                $html->setblockvar('item_' . $field, '');
            }
            $html->parse("pay", true);
            $html->setblockvar('item', '');
        }

        parent::parseBlock($html);
    }

}

$page = new CAdminPay("", $g['tmpl']['dir_tmpl_administration'] . "pay.html");

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include("../_include/core/administration_close.php");
?>