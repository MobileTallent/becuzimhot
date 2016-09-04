<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CAdminSeo extends CHtmlBlock
{
	var $message = '';
    var $table = 'seo';

	function action()
	{
        global $p;

		$cmd = get_param('cmd', '');
        $id = get_param('id');
        $url = trim(get_param('url'));
        $title = trim(get_param('title'));
        $description = trim(get_param('description'));
        $keywords = trim(get_param('keywords'));

        $isSaved = false;
		if($cmd == 'add' && !empty($url)) {
            $isSaved = true;

            $sql = 'INSERT INTO ' . $this->table . '
                       SET url = ' . to_sql($url) . ',
                         title = ' . to_sql($title) . ',
                   description = ' . to_sql($description) . ',
                      keywords = ' . to_sql($keywords);
            DB::execute($sql);
        }

		if ($cmd == 'update') {
            $isSaved = true;
            $sql = 'UPDATE ' . $this->table . '
                       SET url = ' . to_sql($url) . ',
                         title = ' . to_sql($title) . ',
                   description = ' . to_sql($description) . ',
                      keywords = ' . to_sql($keywords) . '
                     WHERE id=' . to_sql($id, 'Number');
			DB::execute($sql);
		}

        if($cmd == 'delete' ) {
            $isSaved = true;
            $sql = 'DELETE FROM ' . $this->table . '
                WHERE id = ' . to_sql($id);
            DB::execute($sql);
        }

		if ($cmd == 'default') {
            $isSaved = true;
            $fields = array(
                'title',
                'description',
                'keywords'
            );
            $options = array();
            foreach($fields as $field) {
                $options[$field] = trim(get_param($field));
            }
            Config::updateAll('seo', $options);
		}


        if($cmd && $isSaved) {
            redirect($p . '?action=saved');
        }

	}

	function parseBlock(&$html)
	{
		$html->setvar('message', $this->message);

        $options = Config::getOptionsAll('seo');
        foreach($options as $key => $value) {
            $html->setvar('seo_' . $key, he($value));
        }

		parent::parseBlock($html);
	}
}

class Cgroups extends CHtmlList
{
	function init()
	{
		parent::init();
		$this->m_on_page = 20;
		$this->m_sql_count = 'SELECT COUNT(*) FROM seo';
		$this->m_sql = 'SELECT * FROM seo';

		$this->m_sql_order = ' id ASC ';
		$this->m_field['id'] = array('id', null);
		$this->m_field['url'] = array('url', null);
		$this->m_field['title'] = array('title', null);
		$this->m_field['description'] = array('description', null);
        $this->m_field['keywords'] = array('keywords', null);
	}

	function onItem(&$html, $row, $i, $last)
	{
        $fields = array(
            'url',
            'title',
            'description',
            'keywords',
        );

        foreach($fields as $field) {
            $this->m_field[$field][1] = htmlentities($row[$field], ENT_QUOTES, 'UTF-8');
        }
	}
}

$page = new CAdminSeo('', $g['tmpl']['dir_tmpl_administration'] . 'seo.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$group_list = new Cgroups("mail_list", null);
$page->add($group_list);

include('../_include/core/administration_close.php');

?>