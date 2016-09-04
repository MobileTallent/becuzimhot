<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");


function do_action()
{
    $pid = ipar('id');
    $uid = ipar('uid');
    Gallery::imageDelete($pid, $uid);
    echo 'ok';
}
do_action();

include("../_include/core/administration_close.php");
