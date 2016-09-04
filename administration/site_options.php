<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

unset($g['options']['audio_approval']);
unset($g['options']['video_approval']);
unset($g['options']['gallery_album_title_length']);
unset($g['options']['gallery_album_description_length']);
unset($g['options']['gallery_image_title_length']);
unset($g['options']['gallery_image_description_length']);
unset($g['options']['profile_photo_description_length']);

if(!Common::isModuleCityExists()) {
    $g['template_options']['hide_site_sections'][] = 'city';
    $g['template_options']['hide_site_sections'][] = 'city_language';
}

class SiteOptions extends CHtmlBlock
{

	function parseBlock(&$html)
	{
        $error = get_param('error' , '');
        $errorMsg = '';
        if ($error != '') {
            $errors = explode('_', $error);
            foreach ($errors as $value) {
                $errorMsg .= l('error_upload_file_' . $value) . '\r\n';
            }
            if ($errorMsg != '') {
                $html->setvar('error', $errorMsg);
                $html->parse('upload_error');
            }
        }
        parent::parseBlock($html);
	}
}

$page = new SiteOptions("", $g['tmpl']['dir_tmpl_administration'] . "site_options.html");

$items = new CAdminConfig("config_fields", $g['tmpl']['dir_tmpl_administration'] . "_config.html");
$items->setModule('options');
$items->setSort('position');
$page->add($items);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuOptions());

include("../_include/core/administration_close.php");