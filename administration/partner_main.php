<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock {

    function lang()
    {
        return Common::langParamValue('lang', 'partner');
    }

    function action()
    {
        global $p;

        $cmd = get_param('cmd', '');
        $id = get_param('id');
        $name = get_param('name');
        $text = get_param('text');
        $lang = $this->lang();

        if ($cmd == 'edit') {
            if($id) {
                $sql = 'UPDATE partner_main
                    SET text = ' . to_sql($text) . '
                    WHERE id = ' . to_sql($id);
            } else {
                $sql = 'INSERT INTO partner_main
                    SET name = ' . to_sql($name) . ',
                    text = ' . to_sql($text) . ',
                    lang = ' . to_sql($lang);
            }
            DB::execute($sql);

            redirect($p . '?action=saved&lang=' . $lang);
        }
    }

    function parseBlock(&$html)
    {
        global $g_options;

        $lang = $this->lang();
        $html->setvar('lang', $lang);

        $html->setvar('langs', adminLangsSelect('partner', $lang));


        $items = array('right_text_over_join_form', 'left_column', 'account_information_in_partner_home');

        foreach ($items as $item) {
            DB::query("SELECT * FROM partner_main WHERE lang = " . to_sql($lang) . " AND name = " . to_sql($item));
            if ($row = DB::fetch_row()) {
                foreach ($row as $k => $v) {
                    $html->setvar($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
                }
            } else {
                $html->setvar('text', '');
                $html->setvar('id', '');
            }

            $html->setvar('name', $item);
            $html->setvar('section_title', l($item));

            $html->parse("question", true);

        }

        parent::parseBlock($html);
    }

}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partner_main.html");

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>