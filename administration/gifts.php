<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CAdminGifts extends CHtmlBlock
{
	function action()
	{
        global $g;

        $cmd = get_param('cmd', '');
        if ($cmd == 'update') {
            $order = get_param('order');
            foreach ($order as $pos => $id) {
                DB::update('gifts', array('position' => to_sql($pos, 'Number')), '`id` = ' . to_sql($id, 'Number'));
            }
            redirect('gifts.php?action=saved');
        } elseif ($cmd == 'delete') {
            $items = get_param('item');
            $set = get_param('set', 1);
            if ($items != '') {
                $items =  explode(',', $items);
                foreach ($items as $id) {
                    DB::delete('gifts', '`id`=' . to_sql($id));
                    $path = ProfileGift::getUrlImg($id, false, false);
                    @unlink($path);
                    DB::delete('user_gift', '`gift` = ' . to_sql($id . '.png'));
                    DB::delete('im_msg', "`msg` LIKE '{gift:%' AND `msg` LIKE " . to_sql('%:' . $id . '}') . " AND `system` = 1");
                }
                redirect("gifts.php?set={$set}&action=delete");
            }
        } elseif ($cmd == 'set_credits') {
            $set = get_param('set', 1);
            $where = '`set` = ' . to_sql($set);
            if ($set == 'all') {
                $where = '';
            }
            $credits = intval(get_param('credits'));
            set_cookie('gifts_price_credits', $credits);
            DB::update('gifts', array('credits' => $credits), $where);
            redirect("gifts.php?set={$set}&action=saved");
        }
	}

	function parseBlock(&$html)
	{
		global $g;


        $priceCredits = get_cookie('gifts_price_credits');
        if ($priceCredits === '') {
            $priceCredits = 50;
        }
        $html->setvar('price_credits', $priceCredits);

        $activeSet = ProfileGift::getActiveSet();
        if (!$activeSet) {
            $activeSet = 'all';
        }
        $set = get_param('set', $activeSet);
        $where = '';
        if ($set != 'all') {
            $where = 'WHERE `set` = ' .to_sql($set);
        }
        $html->setvar('set', $set);
        $listSet = DB::select('gifts_set', '', 'id');
        $listSet = array_merge(array(array('id' => 'all', 'alias' => 'all')), $listSet);
        foreach ($listSet as $item) {
            $html->setvar('list_set_id', $item['id']);
            $html->setvar('list_set_title', ucfirst(l($item['alias'])));
            if ($set == $item['id']) {
                $html->parse('list_set_on', false);
                $html->clean('list_set_off');
                $html->parse('list_set', true);
            } else {
                $html->parse('list_set_off', false);
                $html->clean('list_set_on');
                $html->parse('list_set', true);
            }
        }

        $gifts = DB::all("SELECT * FROM `gifts` {$where} ORDER BY `position`");
        $allowExt = array();
        if (count($gifts)) {
            foreach ($gifts as $gift) {
                $urlImg = ProfileGift::getUrlImg($gift['id'], $gift['hash']);
                if ($urlImg) {
                    $html->setvar('id', $gift['id']);
                    $html->setvar('sent', $gift['sent']);
                    $html->setvar('url_gift', $urlImg);
                    $html->setvar('credits', $gift['credits']);
                    $html->parse('gifts_item', true);
                } else {
                    DB::delete('gifts', '`id`=' . to_sql($gift['id']));
                }
            }
            $html->parse('gifts');
        } else {
            $html->parse('no_gifts');
        }

		parent::parseBlock($html);
	}
}

$page = new CAdminGifts('', $g['tmpl']['dir_tmpl_administration'] . 'gifts.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuGifts());

include('../_include/core/administration_close.php');