<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = "../../";
include($g['to_root'] . "_include/core/main_start.php");

// Если будет неправильный код то вернёт ошибку на профиль в урбане и для старых на upgrade.php
// Check sign
/*if(get_param("transaction_type")!="Purchase") die();
$signSite = md5($pay[$system]['key'].get_param('customer_id').get_param('transaction_id').get_param('transaction_type').get_param('total'));
if(get_param('sign')!=$signSite) die();*/

$custom = get_param('cs1');
echo Pay::getAfterHtml($custom, 'chronopay');