<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CAdminCityAvatarFace extends CHtmlBlock
{
	function action()
	{
        global $g;

        $cmd = get_param('cmd', '');
        if ($cmd == 'update') {
            $order = get_param('order');
            foreach ($order as $pos => $id) {
                DB::update('city_avatar_face_default', array('position' => to_sql($pos, 'Number')), '`id` = ' . to_sql($id, 'Number'));
            }
            redirect('gifts.php?action=saved');
        }/* elseif ($cmd == 'delete') {
            $items = get_param('item');
            if ($items != '') {
                $items =  explode(',', $items);
                foreach ($items as $id) {
                    DB::delete('gifts', '`id`=' . to_sql($id));
                    $path = ProfileGift::getUrlImg($id, false);
                    @unlink($path);
                    DB::delete('user_gift', '`gift` = ' . to_sql($id . '.png'));
                    DB::delete('im_msg', "`msg` LIKE '{gift:%' AND `msg` LIKE " . to_sql('%:' . $id . '}') . " AND `system` = 1");
                }
                redirect('gifts.php?action=delete');
            }
        }*/
	}

	function parseBlock(&$html)
	{
		global $g;

        $faces = DB::all('SELECT * FROM `city_avatar_face_default` ORDER BY `position`');
        if (count($faces)) {
            foreach ($faces as $face) {
                $urlFace = City::getUrlFace($face['id'], 0, $face['gender'], false, true, 'face', true, $face['hash']);
                $html->setvar('id', $face['id']);
                $html->setvar('head_color', $face['head_color']);
                $html->setvar('url_avatar_face', $urlFace);
                $html->parse('avatar_face_item', true);
            }
            $html->parse('avatar_face');
        } else {
            $html->parse('no_avatar_face');
        }

		parent::parseBlock($html);
	}
}

$page = new CAdminCityAvatarFace('', $g['tmpl']['dir_tmpl_administration'] . 'city_avatar_face.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuCity());

include('../_include/core/administration_close.php');