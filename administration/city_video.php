<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CityOptions extends CHtmlBlock
{
    static function getFirstLocation()
	{
        return DB::result('SELECT `id` FROM `city_rooms` WHERE `video` = "1" AND `status` = 1');
    }

	function parseBlock(&$html)
	{
        $first = self::getFirstLocation();
        $curLocation = get_param('loc', $first);
        $html->setvar('cur_location', $curLocation);
        $locations = DB::db_options('SELECT * FROM `city_rooms` WHERE `video` = "1" AND `status` = 1 ORDER BY position', $curLocation);
        $html->setvar('select_location', $locations);
        $html->parse($first ? 'video' : 'no_video');
        parent::parseBlock($html);
	}
}

$page = new CityOptions('', $g['tmpl']['dir_tmpl_administration'] . 'city_video.html');

$items = new CAdminConfig('config_fields', $g['tmpl']['dir_tmpl_administration'] . '_config.html');
$items->setModule('3d_city_video');
$option = 'loc_' . get_param('loc', CityOptions::getFirstLocation());
$items->setAllowedOptions(array($option, $option . '_user'));
$items->setSort('position');
$page->add($items);

$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuCity());

include("../_include/core/administration_close.php");