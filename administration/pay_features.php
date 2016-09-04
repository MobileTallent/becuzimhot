<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include("../_include/core/administration_start.php");

class CPayFeatures extends CHtmlBlock
{

	function action()
	{
		global $p;
        global $g;

		$cmd = get_param('cmd', '');
		if ($cmd == 'update') {
            $status = get_param_array('status');
            $features = DB::select('payment_features');
            foreach ($features as $key => $item) {
                DB::update('payment_features', array('status' => intval(isset($status[$item['id']]))), '`id` = ' . to_sql($item['id']));
            }
			redirect($p . '?action=saved');
		}
	}

	function parseBlock(&$html)
	{
        $features = DB::select('payment_features');
        foreach ($features as $key => $item) {
            $html->setvar('key', $item['id']);
            $html->setvar('checked', $item['status']?'checked':'');
			$html->setvar('title', l($item['title']));
			$html->parse('item', true);
        }

		parent::parseBlock($html);
	}
}

$page = new CPayFeatures('', $g['tmpl']['dir_tmpl_administration'] . 'pay_features.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include('../_include/core/administration_close.php');