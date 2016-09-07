<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = "../../";
include($g['to_root'] . "_include/core/main_start.php");

$system = "paypal";

$item = Pay::checkPlan();
if ($item === false) {
    exit;
}

$urlSite = Common::urlSiteSubfolders();

$returnCancel = $urlSite . 'upgrade.php';
$requestUri = Pay::getRequestUri();
if ($requestUri != '') {
    $returnCancel = $requestUri;
}

$row = Pay::getOptionsPlan($item);
$code = Pay::getCode($item, $row['amount']);

$params = array(
    'cmd' => '_xclick',
    'business' => $pay[$system]['business'],
    'currency_code' => $pay[$system]['currency_code'],
    'item_name' => $row['item_name'],
    'item_number' => $row['item'],
    'amount' => $row['amount'],
    'no_note' => '1',
    'no_shipping' => '1',
    'rm' => '2',
    'return' => $urlSite . '_pay/paypal/after.php',
    'cancel_return' => $returnCancel,
    'custom' => $code,
);

if($pay[$system]['subscription'] == 'Y' && $row['type'] != 'credits') {

    if($row['gold_days'] < 30) {
        $row['period'] = 'D';
        $row['periods'] = $row['gold_days'];
    }

    $periodRules = array(
        'W' => array(7),
        'M' => array(30, 31),
        'Y' => array(365, 366),
    );

    foreach($periodRules as $period => $periodDays) {
        foreach($periodDays as $periodDaysCount) {
            if($row['gold_days'] % $periodDaysCount == 0) {
                $row['period'] = $period;
                $row['periods'] = intval($row['gold_days'] / $periodDaysCount);
            }
        }
    }

    if(isset($row['period']) && isset($row['periods'])) {
        unset($params['amount']);

        $params['cmd'] = '_xclick-subscriptions';
        $params['charset'] = 'utf-8';
        $params['a3'] = $row['amount'];
        $params['p3'] = $row['periods'];
        $params['t3'] = $row['period'];
        $params['src'] = '1';
    }
}

$data = array('item' => $item,
              'system' => $system,
              'code' => $code,
              'request_uri' => $requestUri);
Pay::insertBefore($data);

$demoUrlPrefix = '';

if($pay[$system]['demo'] == 'Y') {
    $demoUrlPrefix = 'sandbox.';
}

$url = "https://www.{$demoUrlPrefix}paypal.com/cgi-bin/webscr";

$paymentUrl = $url . '?' . http_build_query($params);

if(false) {
    echo '<pre>';
    var_export($params);
    echo '<br><br>';
    echo "<a href='$paymentUrl'>$paymentUrl</a><br><br>";
    $acceptUrl = $urlSite . '_pay/paypal/after.php?payment_status=Completed&custom=' . $code;
    echo "Accept: <a href='$acceptUrl' target='_blank'>$acceptUrl</a>";
    die();
}

redirect($paymentUrl);