<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");
require_once("../_include/current/blogs/includes.php");

function do_action()
{
    CBlogsTools::deleteImg(ipar('pid'), ipar('iid'));
    CBlogsTools::updatePostOnlyExistsImgs(ipar('pid'));
    echo 'ok';
}
do_action();

include("../_include/core/administration_close.php");
