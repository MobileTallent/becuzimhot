<?php

include("../_include/core/administration_start.php");

$page = new CAdminOptions('', $g['tmpl']['dir_tmpl_administration'] . 'date_options.html');

$items = new CAdminConfig('config_fields', $g['tmpl']['dir_tmpl_administration'] . '_config.html');
$items->setModule('date_formats');
$items->setSort('option');
$page->add($items);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuOptions());
include("../_include/core/administration_close.php");