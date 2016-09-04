<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CAdminFeatures extends CHtmlBlock
{
	var $message = '';

	function action()
	{
        global $p;

		$cmd = get_param('cmd');
        if ($cmd == 'update') {
			$credits = get_param_array('credits');
			foreach ($credits as $id => $cr) {
                DB::update('payment_price', array('credits' => $cr), '`alias` = ' . to_sql($id));
			}
            redirect($p . '?action=saved');
        }
	}

	function parseBlock(&$html)
	{
		$html->setvar('message', $this->message);

        $sql = 'SELECT * FROM `payment_price` ORDER BY `id` ASC';
		DB::query($sql);
        $block = 'item';
        while ($row = DB::fetch_row()) {
            foreach ($row as $k => $v){
                $html->setvar($k, $v);
            }
            $html->setvar('title_features', l($row['title']));
			$html->parse($block, true);
		}
		parent::parseBlock($html);
	}
}

$page = new CAdminFeatures('', $g['tmpl']['dir_tmpl_administration'] . 'pay_price.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include('../_include/core/administration_close.php');