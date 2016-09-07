<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = '../../';
include($g['to_root'] . '_include/core/main_start.php');

// Если будет неправильный код то вернёт ошибку на профиль в урбане и для старых на upgrade.php
/*$url = Common::urlSiteSubfolders();
$redirect = $url . 'upgrade.php';

// TEST URL
#http://site.com/_pay/2co/after.php?demo=Y&sid=339446&key=172DD4F1300AA621B5C41B2480BF04AC&total=19.95&credit_card_processed=Y&user_id=2&product=4

# http://ablespace.abk-soft.com/dev2/_pay/2co/after.php?demo=Y&sid=339446&key=172DD4F1300AA621B5C41B2480BF04AC&total=19.95&credit_card_processed=Y&user_id=2&product=4

// MAKE PAYMENT

// check payment
if(get_param('demo') == 'Y') {
    $order_number = 1;
} else {
    $order_number = get_param('order_number');
}
$hash = strtoupper(md5($pay['2co']['secret_word'].get_param('sid').$order_number.get_param('total')));
if($hash != get_param('key')) {
    redirect($redirect);
}
if(get_param('credit_card_processed') != 'Y') redirect($redirect);*/

$custom = get_param('product');
echo Pay::getAfterHtml($custom, '2co');