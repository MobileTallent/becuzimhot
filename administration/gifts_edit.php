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

        $cmd = get_param('cmd', '');
        $id = get_param('id', '');
        $credits = intval(get_param('credits'));
        $link = '';
        if ($cmd == 'edit') {
            $link = '&id=' . $id . '&action=edit_gift';
        }
        if ($cmd == 'edit' || $cmd == 'add') {
            //if (empty($credits)) {
                //redirect($p . '?cmd=error_credits' . $link);
            //}
            $set = get_param('set', 1);
            if ($cmd == 'edit') {
                DB::update('gifts', array('credits' => $credits, 'set' => $set), '`id` = ' . to_sql($id));
            } else {
                $position = DB::result('SELECT MAX(position) FROM `gifts`');
                DB::insert('gifts', array('credits' => $credits, 'set' => $set, 'position' => $position + 1));
                $id = DB::insert_id();
            }
            if (isset($_FILES['img'])) {
                $img = $_FILES['img']['tmp_name'];
                if (!empty($img) && Image::isValid($img)) {
                    $image = new uploadImage($img);
                    $imgSz = getimagesize($img);
                    if ($imgSz[0] < 93 ||  $imgSz[1] < 79) {
                        redirect($p . '?cmd=error_image_size' . $link);
                    }
                    if ($image->uploaded) {
                        $path = $g['path']['url_files'] . 'gifts/image';
                        $image->file_safe_name = false;
                        $image->image_resize = true;
                        $image->image_ratio_crop = true;
                        $image->image_convert = 'png';
                        $image->png_compression = 0;
                        $image->image_y = 79;
                        $image->image_x = 93;
                        $image->file_new_name_body = $id;
                        $image->file_new_name_ext = 'png';
                        $image->Process($path);
                        if (!$image->processed) {
                            redirect($p . '?cmd=error_image_upload' . $link);
                        } else {
                            Common::saveFileSize($path . '.png');
                            DB::update('gifts', array('hash' => time()), '`id` = ' . to_sql($id));
                            unset($img);
                        }
                    }
                }
            }
            redirect("gifts.php?set={$set}&action=saved");
        }
	}

	function parseBlock(&$html)
	{

            $id = get_param('id');
            $action = get_param('action');
            $cmd = get_param('cmd');

            $set = '';
            $isParseForm = false;
            if ($id != '' && $action == 'edit_gift') {
                $gift = DB::row('SELECT * FROM `gifts` WHERE `id` = ' . to_sql($id, 'Number'));
                $urlImg = ProfileGift::getUrlImg($gift['id'], $gift['hash']);
                $html->parse('edit_gift_title');
                if ($urlImg) {
                    $html->setvar('id', $gift['id']);
                    $html->setvar('url_gift', $urlImg);
                    $html->setvar('credits', $gift['credits']);
                    $html->parse('edit_form');
                    $html->parse('edit_gift');
                    $isParseForm = true;
                    $set = $gift['set'];
                } else {
                    $html->parse('no_gifts');
                }
            } else {
                $html->parse('add_gift');
                $html->parse('add_form');
                $html->parse('add_gift_title');
                $isParseForm = true;
            }
            if ($isParseForm) {
                $html->setvar('set_opts', DB::db_options('SELECT `id`, `alias` FROM `gifts_set`', $set));
                $html->parse('gift_item');
            }

            if ($cmd == 'error_credits' || $cmd == 'error_image_size') {
                $html->setvar('error', l($cmd));
                $html->parse('error');
            }

            parent::parseBlock($html);
	}
}

$page = new CAdminGiftEdit("", $g['tmpl']['dir_tmpl_administration'] . "gifts_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGifts());

include("../_include/core/administration_close.php");