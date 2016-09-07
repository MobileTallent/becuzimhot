<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = '../../';
include($g['to_root'] . '_include/core/main_start.php');

$system = "ccbill";

$item = Pay::checkPlan();
if ($item === false) {
    exit;
}

$row = Pay::getOptionsPlan($item);
$code = Pay::getCode($item, $row['amount']);

$data = array('item' => $item,
              'system' => $system,
              'code' => $code,
              'request_uri' => Pay::getRequestUri());
Pay::insertBefore($data);

// depend on type

$formName = $pay[$system]['form_name'];

// CUSTOM currency
$pay[$system]['currency_code'] = 840;

// Fix - Invalid Initial Period for credits
if($row['type'] === 'credits') {
    $row['gold_days'] = 2;
}

	$url = 'https://bill.ccbill.com/jpost/signup.cgi?clientAccnum=' . $pay[$system]['client_account_number'] .
	'&clientSubacc=' . $pay[$system]['client_subaccount_number'] .
	'&custom=' . $code .
	'&formName=' . $formName .
	'&formPrice=' . $row['amount'] .
	'&formPeriod=' . $row['gold_days'] .
	'&currencyCode=' . $pay[$system]['currency_code'] .
	'&formDigest=' . ccbillKey(
		$row['amount'], $row['gold_days'],
		$pay[$system]['currency_code'], $pay[$system]['salt']
		);

#$url = $row['ccbill_link'] . '&custom=' . $code;
redirect($url);

function ccbillKey($formPrice, $formPeriod, $currencyCode, $salt,
				   $formRebills = 0, $formRecurringPrice = 0,
				   $formRecurringPeriod = 0)
{
	if($formRebills) {
		$key = md5($formPrice . $formPeriod . $formRecurringPrice
		  . $formRecurringPeriod . $formRebills . $currencyCode . $salt);
	} else {
		$str = $formPrice . $formPeriod . $currencyCode . $salt;
		$key = md5($str);
	}
	return $key;
}

include($g['to_root'] . "_include/core/main_close.php");