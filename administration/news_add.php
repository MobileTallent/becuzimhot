<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAddNews extends CHtmlBlock {

    var $message = "";

    function action()
    {
        $cmd = get_param("cmd", "");

        if ($cmd == "add") {
            $title = get_param("title", "");
            $cat = get_param("cat", "");
            $news_short = get_param("news_short", "");
            $news_long = get_param("news_long", "");
            $cat = get_param("cat", "");
            $lang = get_param("lang", "");
			$root = get_param("root", "");
			$root_id = get_param("root_id", "");
            if($root) {
                $root_id = 0;
            }

            $this->message = "";

            if ($this->message == "") {
                $row = array(
                    'visible' => 'Y',
                    'title' => $title,
                    'cat' => $cat,
                    'news_short' => $news_short,
                    'news_long' => $news_long,
                    'dt' => time(),
                    'lang' => $lang,
                    'root' => $root,
                    'root_id' => $root_id,
                );
                DB::insert('news', $row);

                redirect('news.php?lang=' . $lang);
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g;

        $lang = Common::langParamValue();
        $html->setvar('lang', $lang);

        if ($this->message == 'added') {
            $html->parse('end', true);
        } else {
            $html->setvar("title", get_param("title", ""));
            $cat_options = "<option value='0'>" . l('No category') . "</option>\r\n";
            $sql = "SELECT id, title FROM news_cats WHERE lang = " . to_sql($lang);
            $cat_options .= DB::db_options($sql, get_param("cat", ""));
            $html->setvar("cat_options", $cat_options);
            $html->setvar("news_short", get_param("news_short", ""));
            $html->setvar("news_long", get_param("news_long", ""));

            $root = '';
            if(get_param('root') == 1) {
                $root = 'checked';
            }
            $html->setvar('root', $root);

            if($lang == 'default') {
                $html->parse('root_page');
            }

            $sql = 'SELECT COUNT(*) FROM news
                WHERE root = 1';
            $rootPagesCount = DB::result($sql);

            if($rootPagesCount && $lang != 'default') {
                $rootPagesOptions = "<option value='0'></option>\r\n";
                $rootPagesOptions .= DB::db_options('SELECT id, title FROM news WHERE root = 1 AND id != ' . to_sql(get_param('root_id')));

                $html->setvar('root_pages_options', $rootPagesOptions);

                $html->parse('root_pages');
            }

            $html->parse("form", true);
        }

        parent::parseBlock($html);
    }

}

$page = new CAddNews("", $g['tmpl']['dir_tmpl_administration'] . "news_add.html");

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>