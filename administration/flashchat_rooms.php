<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminFlashchat extends CHtmlBlock
{

	function action()
	{
            $cmd = get_param('cmd', '');
            if ($cmd == 'update') {
                $order = get_param('order');
                $status = get_param('rooms_status');
                foreach ($order as $pos => $id) {
                    $statusRoom = (isset($status[$id]) ) ? 1 : 0;
                    DB::update('flashchat_rooms', array('status' => $statusRoom, 'position' => to_sql($pos, 'Number')), '`id` = ' . to_sql($id, 'Number'));
                }
                redirect('flashchat_rooms.php?action=saved');
            } elseif ($cmd == 'delete') {
                $items = get_param('item');
                if ($items != '') {
                    $items =  explode(',', $items);
                    foreach ($items as $id) {
                        DB::execute('DELETE FROM `flashchat_messages` WHERE `room` = ' . to_sql($id, 'Number'));
                        DB::execute('DELETE FROM `flashchat_users` WHERE `room` = ' . to_sql($id, 'Number'));
                        DB::execute('DELETE FROM `flashchat_rooms` WHERE `id` = ' . to_sql($id, 'Number'));
                    }
                }
            }
	}

	function parseBlock(&$html)
	{
		global $g;
		global $l;

		$lang = str_replace($g['path']['dir_lang'] . "main/", '', $g['lang']['main']);
		$lang = trim($lang, '/');

		$page = l('page');
		$page = str_replace('{lang}', $lang, $page);
		$html->setvar('page', $page);

        $rooms = DB::all('SELECT * FROM `flashchat_rooms` ORDER BY `position`');
        if (count($rooms)) {
            foreach ($rooms as $room) {
                $html->setvar('room_key', $room['id']);
                if ($room['status']) {
                    $html->setvar('checked', 'checked');
                } else {
                    $html->setvar('checked', '');
                }
                $html->setvar('id', $room['id']);
                $html->setvar('name', $room['name']);
                $html->parse('room_item', true);
            }
            $html->parse('rooms');
        } else {
            $html->parse('no_rooms');
        }

		parent::parseBlock($html);
	}
}

$page = new CAdminFlashchat("", $g['tmpl']['dir_tmpl_administration'] . "flashchat_rooms.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
