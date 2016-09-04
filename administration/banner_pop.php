<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CCode extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $id = get_param('id');

        $code = DB::field('banners', 'code','`id` = ' . to_sql($id, 'Number'));
        if (isset($code[0])) {
            $html->setvar('code', $code[0]);
            $html->parse('code_banner');
        } else {
            $html->parse('no_banner');
        }
        parent::parseBlock($html);
	}
}

$page = new CCode('', $g['tmpl']['dir_tmpl_administration'] . 'banner_pop.html');

include('../_include/core/administration_close.php');
?>
