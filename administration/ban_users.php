<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CUsersBan extends CHtmlList
{
    function action()
    {
        global $g;

        $release = get_param('release', 0);
        if ($release != 0) {
            $user =  explode(',', $release);
            foreach ($user as $userId) {
                $row = array('ban_mails' => 0,
                             'ban_time' => '0000-00-00 00:00:00',
                             'ban_time_release' => time());
                DB::update('user', $row, '`user_id`=' . to_sql($userId, 'Number'));
            }
        }

        $del = get_param('delete', 0);
		if ($del != 0) {
            $user =  explode(',', $del);
            foreach ($user as $userId) {
                if (Common::isEnabledAutoMail('admin_delete')) {
                    DB::query('SELECT * FROM `user` WHERE `user_id` = ' . to_sql($userId, 'Number'));
                    $row = DB::fetch_row();
                    $vars = array(
                        'title' => $g['main']['title'],
                        'name' => $row['name'],
                    );
                    Common::sendAutomail($row['lang'], $row['mail'], 'admin_delete', $vars);
                }
                delete_user($userId);
            }
		}
    }

    function init()
    {
        global $g;

        $this->m_on_page = 20;
        $this->m_on_bar = 10;
        $this->m_sql_where = 'ban_mails = 1';
        $this->m_sql_count = 'SELECT COUNT(user_id) FROM `user` ';
        $this->m_sql = 'SELECT `user_id`, `name`, `mail`, `ban_time` FROM `user`  ';

        $this->m_field['user_id'] = array('user_id', null);
        $this->m_field['name'] = array('name', null);
        $this->m_field['ban_time'] = array('ban_time', null);

        $this->m_sql_order = 'ban_time';
    }

    function onPostParse(&$html)
	{
        if ($this->m_total != 0) {
            $html->parse('delete_all');
        }
	}

    function onItem(&$html, $row, $i, $last)
    {
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

$page = new CUsersBan('main', $g['tmpl']['dir_tmpl_administration'] . 'ban_users.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuBlock());

include('../_include/core/administration_close.php');