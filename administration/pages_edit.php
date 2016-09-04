<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$page = get_param('page');
if (!$page || !DB::count('pages', '`id` = ' . to_sql($page) . ' OR `parent` = ' .  to_sql($page))) {
    redirect('pages.php');
}

class CCustomPageEdit extends CHtmlBlock
{
    private $message = '';

	function action()
	{
        global $p;

		$cmd = get_param('cmd');

		if ($cmd == 'save')
		{
            $lang = get_param('lang');
            $id = get_param('page');
            $isSystem = intval(get_param('system'));
            $where = '`parent` = ' . to_sql($id) . ' AND `lang` = ' .  to_sql($lang);
            if ($lang == 'default' || $isSystem) {
                $where = '`id` = ' . to_sql($id);
            }
            $pageKey = get_param('page_key');
			$data = array(
                'menu_title' => trim(get_param('menu_title')),
                'title' => trim(get_param('title')),
                'content' => trim(get_param('content')),
                'section' => get_param('section'),
                'system' => $isSystem,
            );
            if ($isSystem) {
                $data = array('menu_style' => intval(get_param('menu_style')!=''),);
            }
            if (DB::count('pages', $where)) {
                DB::update('pages', $data, $where);
            } else {
                $data['lang'] = $lang;
                $data['parent'] = $id;
                DB::insert('pages', $data, $where);
            }

            if ($isSystem) {
                $this->message = updateLanguage();
            }
            if ($this->message == '') {
                redirect("{$p}?lang={$lang}&page={$id}&action=saved");
            }
        }
	}

	function parseBlock(&$html)
	{
		global $g;

        if ($this->message) {
            $html->setvar('message', $this->message);
            $html->parse('message');
        }

        $lang = Common::getOption('administration', 'lang_value');
        $langTinymceUrl =  $g['tmpl']['url_tmpl_administration'] . "js/tinymce/langs/{$lang}.js";
        if (!file_exists($langTinymceUrl)) {
            $lang = 'default';
        }
        $html->setvar('lang_vw', $lang);

        $id = get_param('page');
        $html->setvar('id', $id);

        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        adminParseLangsModule($html, $languageCurrent);
        $html->parse('block_language');

        $isParentContent = true;
        $isSytem = null;
        $pageKey = 'menu_title';
        $sql = 'SELECT *
                  FROM `pages`
                 WHERE `lang` = ' . to_sql($languageCurrent) .
                 ' AND `parent` = ' . to_sql($id);
        $sqlParent = 'SELECT *
                        FROM `pages`
                       WHERE `id` = ' . to_sql($id);
        if ($languageCurrent == 'default') {
            $isParentContent = false;
            $sql = $sqlParent;
        }

        $lang = loadLanguage(get_param('lang', 'default'), 'main');
        $row = DB::row($sql);
        if ($row) {
            $isSytem = $row['system'];
            $menuTitle = $row['menu_title'];
            if ($isSytem) {
                $pageKey = $menuTitle;
                $menuTitle = he_decode(l($pageKey, $lang));
            }
            $row['menu_title'] = he($menuTitle);
            $html->assign('page', $row);
        } else {
            $row = DB::row($sqlParent);
            $isSytem = $row['system'];
            if ($isSytem) {
                $pageKey = $row['menu_title'];
                $html->setvar('page_menu_title', he(he_decode(l($pageKey, $lang))));
            }
            $html->setvar('page_section', $row['section']);
        }

        $html->setvar('system', intval($isSytem));
        $html->setvar('page_key', $pageKey);

        if ($isSytem) {
            $html->setvar('menu_style_checked', $row['menu_style'] ? 'checked' : '');
            $html->parse('menu_style');
        } else {
            $html->setvar('domain_name', Common::urlSiteSubfolders());
            $html->parse('link_page', false);
            $html->parse('content');
        }

		parent::parseBlock($html);
	}
}

$page = new CCustomPageEdit("", $g['tmpl']['dir_tmpl_administration'] . "pages_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");