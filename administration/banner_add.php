<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$banners_path = $g['path']['url_files'] . "banner/";

class CForm extends CHtmlBlock {

    var $message = '';

    function action()
    {
        global $g;
        global $p;

        $cmd = get_param('cmd');
        if ($cmd == 'add') {
            $type = get_param('type', 'code');
            $nameBanner = trim(get_param('name'));
            $place = str_replace(' ', '_', get_param('place'));
            $active = get_param('active', 0);
            $width = intval(get_param('width'));
            $height = intval(get_param('height'));

            if ($type == 'flash' && ($width == 0 || $height == 0)) {
                $this->message = l('not_correctly_set_the_size_of_the_flash') . "\\n";
            }

            $name = 'banner_img';
            if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name'])) {
                $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
                $typeFile = ($type == 'flash') ? 1 : 2;
                if (!checkBannerExt($ext, $typeFile)) {
                    $this->message .= ($type == 'flash') ? l('file_must_be_formats_flash') : l('file_must_be_formats_image');
                    $this->message .= "\\n";
                }
            } elseif ($type != 'code') {
                    $this->message .= l('file_is_not_selected') . "\\n";
            }

            if ($this->message == '') {
                if ($type == 'code') {
                    $sqlPart = 'code = ' . to_sql(get_param('code')) . ',';
                } elseif ($type == 'flash') {
                    $sqlPart =  'width = ' . to_sql($width, 'Number') . ','
                              . 'height = ' . to_sql($height, 'Number') . ',';
                } else {
                    $sqlPart =  'url = ' . to_sql(get_param('url')) . ','
                              . 'alt = ' . to_sql(get_param('alt')) . ',';
                }

                $sql = 'INSERT INTO banners '
                        . 'SET active = ' . to_sql($active) . ','
                        . 'name = ' . to_sql($nameBanner) . ','
                        . 'type = ' . to_sql(get_param('type')) . ','
                        . $sqlPart
                        . 'place = ' . to_sql($place) . ','
                        . 'templates = ' . to_sql(get_param('template')) . ','
                        . 'langs = ' . to_sql(get_param('lang'));
                DB::execute($sql);

                if ($type != 'code') {
                    $id = DB::insert_id();
                    $file = "{$id}.{$ext}";
                    $filePatch = "{$g['path']['dir_files']}banner/{$file}";
                    move_uploaded_file($_FILES[$name]['tmp_name'], $filePatch);
                    Common::saveFileSize($file);
                    DB::update('banners', array('filename' => $file), '`id` = ' . to_sql($id, 'Number'));
                }

                redirect('banner.php');
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $l;
        global $banners_path;

        if ($this->message != '') {
            $html->setvar('message', $this->message);
            $html->parse('alert', true);
        }

        $type = get_param('type', 'code');
        if ($type == 'code') {
            $html->parse('field_code');
        } elseif ($type == 'flash') {
            $html->parse('fields_flash');
            $html->parse('field_file');
        } else {
            $html->parse('fields');
            $html->parse('field_file');
        }
        $html->parse('selected_' . $type);

        $whereNotAllow = CBanner::getAllowBannerSql();
        $places = strtolower(DB::db_options('SELECT place AS id, place AS title FROM banners_places' . $whereNotAllow, ''));
        $html->setvar('place_opts', str_replace('_', ' ', $places));

        $tmpls = array();
        if (countFrameworks('main')) {
           $tmpls = Common::listTmpls('main');
        }
        if (countFrameworks('mobile')) {
           $tmpls = array_merge($tmpls, Common::listTmpls('mobile'));
        }
        $templates = array(l('All') => '') + $tmpls;
        $templates = array_flip($templates);
        $tmplOptions = h_options($templates, '');
        $html->setvar("template_opts", $tmplOptions);

        $langs = Common::listLangs();
        $langs = array(l('All') => '') + $langs;
        $langs = array_flip($langs);
        $langOptions = h_options($langs, '');
        $html->setvar('language_opts', $langOptions);


        parent::parseBlock($html);
    }

}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "banner_add.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>
