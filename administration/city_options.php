<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CityOptions extends CHtmlBlock
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

$page = new CityOptions('', $g['tmpl']['dir_tmpl_administration'] . 'city_options.html');

$items = new CAdminConfig('config_fields', $g['tmpl']['dir_tmpl_administration'] . '_config.html');
$items->setModule('3d_city');
$items->setSort('position');
$page->add($items);

$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuCity());

include("../_include/core/administration_close.php");