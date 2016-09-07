<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = '../../';
include($g['to_root'] . '_include/core/main_start.php');

$system = 'chronopay';

$item = Pay::checkPlan();
if ($item === false) {
    exit;
}

$siteUrl = Common::urlSiteSubfolders();
$successUrl = $siteUrl . '/' . 'upgraded.php';
$declineUrl = $siteUrl . '/' . 'upgrade.php';

$requestUri = Pay::getRequestUri();
if ($requestUri != '') {
    $successUrl = $declineUrl = $requestUri;
}

$row = Pay::getOptionsPlan($item);

$code = Pay::getCode($item, $row['amount']);

$data = array('item' => $item,
              'system' => $system,
              'code' => $code,
              'request_uri' => $requestUri);
Pay::insertBefore($data);

$product_id = $pay['chronopay']['product_id'];
$product_price = $row['amount'];
$sign = md5($product_id . '-' . $product_price . '-' . $pay['chronopay']['key']);

$cb_url = urlencode($siteUrl . '/_pay/chronopay/after.php');
$success_url = urlencode($successUrl);
$decline_url = urlencode($declineUrl);

$url = "https://payments.chronopay.com/?product_id=$product_id&product_price=$product_price&cb_url=$cb_url&success_url=$success_url&decline_url=$decline_url&sign=$sign&cs1=$code";

redirect($url);