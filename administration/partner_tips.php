<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$page = new CAdminParnerPage("", $g['tmpl']['dir_tmpl_administration'] . "partner_pages.html");
$page->setTable('tips');

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>