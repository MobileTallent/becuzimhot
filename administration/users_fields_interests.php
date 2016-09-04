<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CInterestsResults extends CHtmlList
{
	function action()
	{
        global $p;

        $cmd = get_param('cmd');
        $category = get_param('category');
        $lang = get_param('lang');
        $params = '';
        if ($cmd) {
            if ($category) {
                $params = "&category={$category}";
            }
            if ($lang) {
                $params .= "&lang={$lang}";
            }
        }
        if ($cmd == 'add') {
            $interests = get_param('interests');
            Interests::addInterest($category, $interests, $lang, 0);
            redirect("{$p}?action=saved{$params}&order=id&sort=desc");
        } elseif ($cmd == 'delete') {
            $del = get_param('delete', 0);
            if ($del){
                $interests =  explode(',', $del);
                foreach ($interests as $id) {
                    Interests::deleteFullInterest($id);
                }
                redirect("{$p}?action=delete{$params}");
            }
        }
	}

	function init()
	{
		global $g;

        $this->m_parse_params_for_empty = true;

        $this->m_on_page = 50;
		$this->m_on_bar = 10;

		$this->m_sql_count = 'SELECT COUNT(I.id) FROM `interests` AS I ' . $this->m_sql_from_add;
		$this->m_sql = 'SELECT I.* FROM `interests` AS I ' . $this->m_sql_from_add;

		$this->m_field['id'] = array('id', null);
        $this->m_field['user_id'] = array('user_id', null);
        $this->m_field['category'] = array('category', null);
        $this->m_field['interest'] = array('interest', null);
        $this->m_field['counter'] = array('counter', null);
        $this->m_field['lang'] = array('lang', null);

        $cat = get_param('category', 0);
        $where = '';
        if ($cat) {
            $where = ' AND `category` = ' . to_sql($cat);
        }
        $lang = get_param('lang');
        if ($lang) {
            $where .= ' AND `lang` = ' . to_sql($lang);
        }
		$this->m_sql_where = '1' . $where;
		$this->m_sql_order = 'id';
		$this->m_sql_from_add = '';
	}

	function parseBlock(&$html)
	{
        $html->setvar('select_options_language_add_interest', adminLangsSelect('main', get_param('lang', Common::getOption('main', 'lang_value'))));
        $html->setvar('select_options_category_add_interest', DB::db_options('SELECT `id`, `title` FROM `const_interests`', get_param('category')));

		parent::parseBlock($html);
	}

    function onPostParse(&$html)
	{
        $html->setvar('page_params', del_param('offset', get_params_string()));
        $cat = get_param('category', 'all');
        $category = DB::select('const_interests', '', 'id');
        $category = array_merge(array(array('id' => 0, 'title' => 'all')), $category);
        foreach ($category as $item) {
            $html->setvar('list_category_id', $item['id']);
            $html->setvar('list_category_title', ucfirst(l($item['title'])));
            if ($cat == $item['id']) {
                $html->parse('list_category_on', false);
                $html->clean('list_category_off');
                $html->parse('list_category', true);
            } else {
                $html->parse('list_category_off', false);
                $html->clean('list_category_on');
                $html->parse('list_category', true);
            }
        }

        $lang = get_param('lang');
        $html->setvar('select_options_language', adminLangsSelect('main', $lang));

        if ($this->m_total != 0) {
            $html->parse('no_delete');
        }
	}

	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        $lang = $row['lang'];
        if ($lang == 'default') {
            $lang = 'English';
        }
        $this->m_field['lang'][1] = l($lang);

        $html->setvar('user_name', User::getInfoBasic($row['user_id'], 'name', 1));
        $html->setvar('title_category', Interests::getTitleCategory($row['category']));

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CInterestsResults('main', $g['tmpl']['dir_tmpl_administration'] . 'users_fields_interests.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");