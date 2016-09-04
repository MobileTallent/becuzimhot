<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminGiftEdit extends CHtmlBlock
{

	var $message = "";

	function action()
	{
        global $g;
        global $p;

        $cmd = get_param('cmd');
        $id = get_param('id');
        if ($cmd == 'set') {
            DB::update('gifts_set', array('active' => 0));
            DB::update('gifts_set', array('active' => 1), '`id` = ' . to_sql($id));
            redirect('gifts_set.php?action=saved');
        }
	}

	function parseBlock(&$html)
	{

        $listSet = DB::select('gifts_set', '', 'id');
        $active = 0;
        $listSetOpt = array(0 => l('all'));
        foreach ($listSet as $item) {
            $urlImg = ProfileGift::getUrlImg($item['id'], false, false, 'set');
            if ($urlImg) {
                $listSetOpt[$item['id']] = l($item['alias']);
                $html->setvar('id', $item['id']);
                $html->setvar('title', $item['alias']);
                $html->setvar('url_set', $urlImg);
                $html->parse('sets_item', true);
            } else {
                DB::delete('gifts_set', '`id`=' . to_sql($listSet['id']));
            }
            if ($item['active']) {
                $active = $item['id'];
            }
        }
        $html->setvar('set_opts', h_options($listSetOpt, $active));
        $html->parse('sets');


        parent::parseBlock($html);
	}
}

$page = new CAdminGiftEdit("", $g['tmpl']['dir_tmpl_administration'] . "gifts_set.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGifts());

include("../_include/core/administration_close.php");