<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock {

    function action()
    {
        global $g_options;
        $cmd = get_param('cmd', '');
        $lang = Common::langParamValue();

        $root = get_param('root', '');
        $root_id = get_param('root_id', '');
        if($root) {
            $root_id = 0;
        }

        if ($cmd == "delete") {
            DB::execute("
				DELETE FROM news_cats
				WHERE id=" . to_sql(get_param("id", ""), "Number") . "
			");
        } elseif ($cmd == "edit") {
            DB::execute("
				UPDATE news_cats
				SET
				title=" . to_sql(get_param("title", ""), "Text") . ",
				root=" . to_sql($root) . ",
				root_id=" . to_sql($root_id) . "
				WHERE id=" . to_sql(get_param("id", ""), "Number") . "
			");
        } elseif ($cmd == "add") {
            DB::execute("
				INSERT INTO news_cats
				SET
				title=" . to_sql(get_param("title", ""), "Text") . ",
                lang = " . to_sql($lang) . ",
				root=" . to_sql($root) . ",
				root_id=" . to_sql($root_id) . "
			");
        }

        if ($cmd == 'edit') {
            global $p;
            redirect("$p?action=saved&lang=$lang");
        }
    }

    function parseBlock(&$html)
    {
        global $g_options;

        $lang = Common::langParamValue();
        $html->setvar('lang', $lang);

        $langs = Common::listLangs();
        if ($langs) {

            foreach ($langs as $title => $file) {
                $html->setvar('language_value', $file);
                $html->setvar('language_title', $title);
                if ($file == $lang) {
                    $html->parse('language_active', false);
                    $html->setblockvar('language_link', '');
                } else {
                    $html->parse('language_link', false);
                    $html->setblockvar('language_active', '');
                }
                $html->parse('language');
            }
        }

        DB::query("SELECT * FROM news_cats WHERE lang = " . to_sql($lang) . " ORDER BY id", 2);
        while ($row = DB::fetch_row(2)) {
            foreach ($row as $k => $v) {
                $html->setvar($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
            }

            $root = '';
            if(get_param('root', $row['root']) == 1) {
                $root = 'checked';
            }
            $html->setvar('root', $root);

            if($lang == 'default') {
                $html->parse('root_page', false);
            }

            $id = $row['id'];

            $sql = 'SELECT COUNT(*) FROM news_cats
                WHERE root = 1 AND id != ' . to_sql($id);
            $rootPagesCount = DB::result($sql);

            if($rootPagesCount && $lang != 'default') {
                $rootPagesOptions = "<option value='0'></option>\r\n";
                $rootPagesOptions .= DB::db_options('SELECT id, title FROM news_cats WHERE root = 1 AND id != ' . to_sql($id), $row['root_id']);

                $html->setvar('root_pages_options', $rootPagesOptions);

                if(!$root) {
                    $html->parse('root_pages', false);
                } else {
                    $html->setblockvar('root_pages', '');
                }
            }

            $html->parse("question", true);
        }

        $html->setvar("title", get_param("title", ""));

        $sql = 'SELECT COUNT(*) FROM news_cats
            WHERE root = 1';
        $rootPagesCount = DB::result($sql);

        if($rootPagesCount && $lang != 'default') {
            $rootPagesOptions = "<option value='0'></option>\r\n";
            $rootPagesOptions .= DB::db_options('SELECT id, title FROM news_cats WHERE root = 1', '');

            $html->setvar('root_pages_add_options', $rootPagesOptions);

            $html->parse('root_pages_add');
        }

        if($lang == 'default') {
            $html->parse('root_page_add', false);
        }

        parent::parseBlock($html);
    }

}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "news_cats.html");

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>
