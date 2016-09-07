<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = "../../";
include($g['to_root'] . "_include/core/main_start.php");
global $g;

$demo="N";
if(isset($pay['2co']['demo']) && $pay['2co']['demo']=="Y") $demo="Y";

$item = Pay::checkPlan();
if ($item === false) {
    exit;
}

$row = Pay::getOptionsPlan($item);
$code = Pay::getCode($item, $row['amount']);

$system = "2co";

$data = array('item' => $item,
              'system' => $system,
              'code' => $code,
              'request_uri' => Pay::getRequestUri());
Pay::insertBefore($data);

$url = 'https://www.2checkout.com/2co/buyer/purchase?sid='
		. $pay[$system]['business'] . '&quantity=1&product_id='
		. $row['co_id']
		. '&demo='.$demo
		. '&user_id=' . $g_user['user_id']
		. '&product=' . $code
		. '&tco_currency=' . $pay[$system]['currency_code'];

redirect($url);