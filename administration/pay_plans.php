<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CAdminPay extends CHtmlBlock
{

	var $message = '';

	function action()
	{
		global $g;
		global $pay;
        global $p;

		$cmd = get_param('cmd');
        $item = get_param('item');

        if ($cmd == 'add' || $cmd == 'update') {
            $item_name = get_param('item_name');
            if ($item_name != l('new_plan') && $item_name != l('credits_plans')) {
                $type = get_param('type');
                $pays = get_param('pay',array());
                $vars = array('item_name' => $item_name,
                              'amount' => get_param('amount'),
                              'gold_days' => get_param('gold_days'),
                              'type' =>  $type,
                              'co_id' => get_param('co_id'),
                              'fortumo_service_id' => get_param('fortumo_service_id'),
                              'fortumo_secret' => get_param('fortumo_secret'),
                              'set' => Common::getOption('set', 'template_options'));
                $set = Common::getOption('set', 'template_options');
                if ($set == 'urban') {
                    $defaultItem = get_param('default_plan');
                    DB::update('payment_plan', array('default' => 0), '`type` = ' . to_sql($type));
                    if ($defaultItem != 'add') {
                        DB::update('payment_plan', array('default' => 1), '`item` = ' . to_sql($defaultItem));
                    } else {
                        $vars['default'] = 1;
                    }
                }

                $paymentModules = $g['payment_modules'];
                $paymentModulesOffArr=array();
                foreach ($paymentModules as $key => $value) {
                    if(!isset($pays[$key])){
                        $paymentModulesOffArr[]=$key;
                    }
                }
                $vars['payment_modules_off']=implode(',',$paymentModulesOffArr);
                if ($cmd == 'add') {
                    DB::insert('payment_plan', $vars);
                } else {
                    DB::update('payment_plan', $vars, '`item` = ' . to_sql($item, 'Number'));
                }
            }
            //redirect($p . '?action=saved');
        } elseif($cmd == 'delete' && $item) {
            DB::delete('payment_plan', '`item` = ' . to_sql($item, 'Number'));
            redirect($p . '?action=delete');
        }
	}

    function parsePlan(&$html, $set, $where, $customType = '')
	{
		global $g;

        $types = array(
            'silver' => l('Silver'),
            'gold' => l('Gold'),
            'platinum' => l('Platinum'),
        );

        $sql = 'SELECT *
                  FROM `payment_plan`
                 WHERE `set` = ' . to_sql($set) . ' '
                 . $where .
               ' ORDER BY `item` ASC';
		DB::query($sql);
        $block = 'item';
        $blockType = $block . '_type';

        $html->clean($block);
        $goldDaysLabel = ($customType == 'credits') ? l('credits') : l('gold_days');
        $html->setvar('gold_days_label', $goldDaysLabel);

        if ($customType == 'credits') {
            $html->setvar('paid_days_length', '');
        } else {
            $html->setvar('paid_days_length', Common::getOption('paid_days_length'));
        }

        $paymentModules = $g['payment_modules'];
        $blockInner = 'pay';

        while ($row = DB::fetch_row()) {
            foreach ($row as $k => $v){
                if ($k == 'item_name') {
                    $v = he($v);
                }
                $html->setvar($k, $v);
            }
            $paymentModule = '';
            if ($set == 'urban') {
                if ($row['default']) {
                    $html->setvar('default_item', $row['item']);
                    $html->setvar('checked', 'checked');
                } else {
                    $html->setvar('checked', '');
                }
                $html->parse($block . '_default', false);
                $html->parse($block . '_default_add', false);
                $paymentModule = '_urban';
                $vars = array('item' => l($row['item_name']),
                              'currency_sign' => l('currency_sign'),
                              'amount' => $row['amount']);
                $html->parse($blockType . '_const', false);
                $nameTitle = lSetVars('payment_module' . $paymentModule, $vars);
            } else {
                $html->setvar('select_type', h_options($types, $row['type']));
                $html->parse($blockType, false);
                $nameTitle = l($row['type']) . ' ' . l($row['item_name']) . ' - ' . $row['amount'];
            }

            $html->clean($blockInner .'_item');
            $paymentModulesOffArr=array_flip(explode(',',$row['payment_modules_off']));
            foreach ($paymentModules as $key => $value) {
                $html->setvar($blockInner . '_key', $key);
                if (!isset($paymentModulesOffArr[$key])){
                    $html->setvar($blockInner . '_checked', 'checked');
                } else{
                    $html->setvar($blockInner . '_checked', '');
                }
                $html->setvar($blockInner . '_title', $value.' '.l($key));
                $html->parse($blockInner . '_item', true);
            }
            reset($paymentModules);

            $html->setvar('item_name_title', $nameTitle);
			$html->parse($block, true);
		}

        $blockTypeAdd = $block . '_type_add';

        $html->clean($blockInner .'_item_add');
        foreach ($paymentModules as $key => $value) {
            $html->setvar($blockInner . '_key', $key);
            $html->setvar($blockInner . '_checked', 'checked');
            $html->setvar($blockInner . '_title', $value.' '.l($key));
            $html->parse($blockInner . '_item_add', true);
        }

        if ($set == 'urban') {
            $html->setvar('type', $customType);
            $html->parse($blockTypeAdd . '_const', false);
        } else {
            $html->setvar('add_select_type', h_options($types, ''));
            $html->parse($blockTypeAdd, false);
        }

        if ($customType == '' || $customType == 'membership') {
            $html->setvar('new_plan', l('new_plan'));
            $html->setvar('title_plans', l('title_current'));
        } elseif ($customType == 'credits') {
            $html->setvar('new_plan', l('new_credits_plan'));
            $html->setvar('title_plans', l('credits_plans'));
        }

        $html->parse('items');
    }

	function parseBlock(&$html)
	{
		global $g;
		global $pay;
		global $p;

		$html->setvar('message', $this->message);

        $where = '';
        $set = Common::getOption('set', 'template_options');

        if ($set == 'urban') {
            $customType = array('membership', 'credits');
            foreach ($customType as $value) {
                $where = ' AND `type` = ' . to_sql($value);
                $this->parsePlan($html, $set, $where, $value);
            }
        } else {
            $this->parsePlan($html, $set, $where);
        }

        $html->setvar('paid_days_length', Common::getOption('paid_days_length'));

		parent::parseBlock($html);
	}
}

$page = new CAdminPay('', $g['tmpl']['dir_tmpl_administration'] . 'pay_plans.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include('../_include/core/administration_close.php');