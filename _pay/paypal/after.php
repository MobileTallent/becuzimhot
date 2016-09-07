<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = "../../";
include($g['to_root'] . "_include/core/main_start.php");

$custom = get_param('custom', '');
$cm = get_param('cm', '');

// Fix - sometimes paypal use "cm" instead of "custom"
if($custom === '' && $cm !== '') {
    $custom = $cm;
}

echo Pay::getAfterHtml($custom, 'paypal');