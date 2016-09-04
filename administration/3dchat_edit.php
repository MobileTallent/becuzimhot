<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdmin3Dchat extends CHtmlBlock
{
	var $message = '';

	function action()
	{
            global $p;

            $cmd = get_param('cmd', '');
            $id = get_param('id', '');
            $name = trim(get_param('name'));
            $link = '';

            if ($cmd == 'edit' || $cmd == 'add') {
                $sql = 'SELECT `id` FROM `chat_room` WHERE LCASE(name) = ' . to_sql(mb_strtolower($name, 'UTF-8'));
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

            if ($cmd == 'add' || $cmd == 'update_bg') {
                if (!isset($_FILES['bg'])) {
                    if ($cmd == 'add') {
                        redirect($p . '?cmd=error_background_is_not_selected');
                    } else {
                        $response['status'] = 0;
                        $response['msg'] = l('error_background_is_not_selected');
                        die(json_encode($response));
                    }
                }
                if (isset($_FILES['bg'])
                    && $_FILES['bg']['error'] == 0
                    && Image::isValid($_FILES['bg']['tmp_name'])) {
                    $image = new uploadImage($_FILES['bg']);
                    $imgSz = getimagesize($_FILES['bg']['tmp_name']);
                    if ($imgSz[0] < 746 || $imgSz[1] < 426) {
                        if ($cmd == 'add') {
                            redirect($p . '?cmd=error_upload_file');
                        } else {
                            $response['status'] = 0;
                            $response['msg'] = l('error_upload_file');
                            die(json_encode($response));
                        }
                    } else {
                        $image->image_convert  = 'jpg';
                        $image->image_resize     = true;
                        $image->image_ratio_crop = true;
                        $image->image_y = 426;
                        $image->image_x = 746;
                        $image->file_new_name_ext = 'jpg';
                    }
                } else {
                    if ($cmd == 'add') {
                        redirect($p . '?cmd=error_upload_file_8');
                    } else {
                        $response['status'] = 0;
                        $response['msg'] = l('error_upload_file_8');
                        die(json_encode($response));
                    }

                }
            }

            if ($cmd == 'add'){
                $position = DB::result('SELECT MAX(position) FROM `chat_room`');
                DB::execute("INSERT INTO `chat_room` SET `name` = " . to_sql($name) . ', `position` = ' . to_sql($position + 1, 'Number'));
                $id = DB::insert_id();
            } elseif ($cmd == 'edit') {
                DB::execute("UPDATE `chat_room` SET `name` = " . to_sql($name) . ' WHERE `id` = ' . to_sql($id, 'Number'));
            }

            if ($cmd == 'add' || $cmd == 'update_bg'){
                $image->file_new_name_body = $id;
                $url = Common::getOption('url_files', 'path') . '3dchat/';
                $filename = "{$url}{$id}.jpg";
                if (file_exists($filename)) {
                    Common::saveFileSize($filename, false);
                    unlink($filename);
                } else {
                    Common::saveFileSize($filename);
                }
                if (!$image->uploaded) {
                    redirect($p . '?cmd=Error: ' . $image->error);
                }
                $image->Process($url);
                if (!$image->processed) {
                    redirect($p . '?cmd=Error: ' . $image->error);
                }
                unset($image);
                if ($cmd == 'update_bg') {
                    $response['url'] = $filename;
                    die(json_encode($response));
                }
            }

            if ($cmd == 'add' || $cmd == 'edit'){
                redirect('3dchat_rooms.php?action=saved');
            }

	}

	function parseBlock(&$html)
	{

            $id = get_param('id');
            $action = get_param('action');
            $cmd = get_param('cmd');

            if ($id != '' && $action == 'edit_room') {
                $room = DB::row('SELECT `name` FROM `chat_room` WHERE `id` = ' . to_sql($id, 'Number'));
                $html->setvar('id', $id);
                $html->setvar('name', he($room['name']));
                $html->parse('edit_form');
                $html->parse('edit_rooms');
                $html->parse('edit_rooms_title');
                $html->setvar('rand', rand(0, 100000));
                $html->parse('upload_rooms_bg');
                $html->parse('upload_rooms_bg_js');
            } else {
                $html->parse('add_rooms');
                $html->parse('add_form');
                $html->parse('add_rooms_title');
            }

            if (strpos(mb_strtolower($cmd, 'UTF-8'), 'error') !== false) {
                $html->setvar('error', l($cmd));
                $html->parse('error');
            }

            parent::parseBlock($html);
	}
}

$page = new CAdmin3Dchat("", $g['tmpl']['dir_tmpl_administration'] . "3dchat_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
