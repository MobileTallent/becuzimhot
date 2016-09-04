<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include("../_include/core/administration_start.php");

class CPayOrder extends CHtmlBlock
{

	function action()
	{
		global $p;
        global $g;

		$cmd = get_param('cmd', '');
		if ($cmd == 'update') {
            $position = get_param_array('pay');
            $status = get_param_array('pay_status');
            $position = array_flip($position);
            foreach ($g['payment_modules'] as $key => $value) {
               Config::updatePosition('payment_modules', $key, $position[$key]);
               //$resultStatus[$key] = (isset($status["$key"])) ? 1 : 0;
            }
            //Config::updateAll('payment_modules', $resultStatus);
			redirect($p . '?action=saved');
		}
	}

	function parseBlock(&$html)
	{
        global $g;

        $paymentModules = $g['payment_modules'];
        $block = 'pay';
        foreach ($paymentModules as $key => $value) {
            $html->setvar($block . '_key', $key);
            if ($value)
                $html->setvar($block . '_checked', 'checked');
            else
                $html->setvar($block . '_checked', '');
			$html->setvar($block . '_title', l($key));
			$html->parse($block . '_item', true);

        }

		parent::parseBlock($html);
	}
}

$page = new CPayOrder('', $g['tmpl']['dir_tmpl_administration'] . 'pay_order.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include('../_include/core/administration_close.php');