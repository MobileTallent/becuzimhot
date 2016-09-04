<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminFlashchat extends CHtmlBlock
{

	var $message = "";

	function action()
	{
            global $p;

            $cmd = get_param('cmd', '');
            $id = get_param('id', '');
            $name = trim(get_param('name'));
            $link = '';

            if ($cmd == 'edit' || $cmd == 'add') {
                $sql = 'SELECT `id` FROM `flashchat_rooms` WHERE LCASE(name) = ' . to_sql(mb_strtolower($name, 'UTF-8'));
                if ($cmd == 'edit') {
                    $sql .= ' AND `id` != ' . to_sql($id, 'Number');
                    $link = '&action=edit_room&id=' . $id;
                }
                if (empty($name)) {
                    redirect($p . '?cmd=error_empty' . $link);
                }
                $room = DB::result($sql);
                if ($room) {
                    redirect($p . '?cmd=error' . $link);
                }
            }
            if ($cmd == 'add'){
                $position = DB::result('SELECT MAX(position) FROM `flashchat_rooms`');
                DB::execute("INSERT INTO `flashchat_rooms` SET `name` = " . to_sql($name) . ', `position` = ' . to_sql($position + 1, 'Number'));
                redirect('flashchat_rooms.php?action=saved');
            } elseif ($cmd == 'edit') {
                DB::execute("UPDATE `flashchat_rooms` SET `name` = " . to_sql($name) . ' WHERE `id` = ' . to_sql($id, 'Number'));
                redirect('flashchat_rooms.php?action=saved');
            }
	}

	function parseBlock(&$html)
	{

            $id = get_param('id');
            $action = get_param('action');
            $cmd = get_param('cmd');

            if ($id != '' && $action == 'edit_room') {
                $room = DB::row('SELECT `name` FROM `flashchat_rooms` WHERE `id` = ' . to_sql($id, 'Number'));
                $html->setvar('id', $id);
                $html->setvar('name', he($room['name']));
                $html->parse('edit_form');
                $html->parse('edit_rooms');
                $html->parse('edit_rooms_title');
            } else {
                $html->parse('add_rooms');
                $html->parse('add_form');
                $html->parse('add_rooms_title');
            }
            if ($cmd == 'error' || $cmd == 'error_empty') {
                $html->setvar('error', l($cmd . '_room'));
                $html->parse('error');
            }

            parent::parseBlock($html);
	}
}

$page = new CAdminFlashchat("", $g['tmpl']['dir_tmpl_administration'] . "flashchat_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
