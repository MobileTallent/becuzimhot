<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CCustomPageAdd extends CHtmlBlock {

    function action()
    {
        global $p;

        $cmd = get_param('cmd');

        if ($cmd == 'save') {
            $maxPosition = DB::result("SELECT MAX(position) FROM `pages` WHERE `lang` = 'default'");
            $data = array(
                'section' => get_param('section'),
                'menu_title' => trim(get_param('menu_title')),
                'title' => trim(get_param('title')),
                'content' => trim(get_param('content')),
                'position' => $maxPosition + 1,
                'lang' => 'default',
            );
            DB::insert('pages', $data);
            redirect('pages.php?action=saved');
        }
    }

    function parseBlock(&$html)
    {
        global $g;

        $lang = Common::getOption('administration', 'lang_value');
        $langTinymceUrl =  $g['tmpl']['url_tmpl_administration'] . "js/tinymce/langs/{$lang}.js";
        if (!file_exists($langTinymceUrl)) {
            $lang = 'default';
        }
        $html->setvar('lang_vw', $lang);

        $html->parse('block_menu');

        $section = array('narrow' => l('narrow'), 'bottom' => l('bottom'), 'not_in_menu' => l('not_in_menu'));
        $html->setvar('options_section', h_options($section, 'narrow'));
        $html->parse('block_section');

        $html->setvar('page_key', 'menu_title');
        $html->parse('content');
        parent::parseBlock($html);
    }

}

$page = new CCustomPageAdd("", $g['tmpl']['dir_tmpl_administration'] . "pages_edit.html");

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuCustomPages());

include("../_include/core/administration_close.php");