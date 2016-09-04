<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminIp extends CHtmlBlock
{

	var $message = "";
    var $table = 'ip_block';

	function action()
	{
		global $g;
		global $pay;
		global $l;
		global $p;

		$cmd = get_param('cmd', '');

        $ip = trim(get_param('ip'));
        $id = trim(get_param('id'));
        $description = trim(get_param('description'));
        $url = trim(get_param('url'));

		if ($cmd == 'update') {
            $sql = 'UPDATE ' . $this->table . '
                SET ip = ' . to_sql($ip, 'Text') . ',
                    description = ' . to_sql($description, 'Text') . '
                    WHERE id = ' . to_sql($id, 'Number');
			DB::execute($sql);
		}

		if($cmd == 'delete' ) {
            $sql = 'DELETE FROM ' . $this->table . '
                WHERE id = ' . to_sql($id);
            DB::execute($sql);
        }

		if($cmd == 'add' ) {
            $sql = 'INSERT INTO ' . $this->table . '
                SET ip = ' . to_sql($ip, 'Text') . ',
                    description = ' . to_sql($description, 'Text');
            DB::execute($sql);
        }

		if($cmd == 'url' ) {
            $sql = 'UPDATE config
                SET value = ' . to_sql($url, 'Text') . '
                WHERE module = "ipblock"
                    AND `option` = "url"';
            DB::execute($sql);
        }

        if($cmd) {
            redirect($p . '?action=saved');
        }
	}

	function parseBlock(&$html)
	{
		$html->setvar("message", $this->message);

        $sql = 'SELECT * FROM config
            WHERE module = "ipblock"
                AND `option` = "url"';

		DB::query($sql);
		while ($row = DB::fetch_row())
		{
            $url = htmlentities($row['value'], ENT_QUOTES, 'UTF-8');
            $html->setvar('url', $url);
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
		$this->m_sql_count = 'select count(*) from ip_block';
		$this->m_sql = 'select * from ip_block';

		$this->m_sql_order = ' id ASC ';
		$this->m_field['id'] = array('id', null);
		$this->m_field['ip'] = array('ip', null);
		$this->m_field['description'] = array('description', null);
	}

	function onItem(&$html, $row, $i, $last)
	{
        $this->m_field['description'][1] = htmlentities($row['description'], ENT_QUOTES, 'UTF-8');
	}
}

$page = new CAdminIp("", $g['tmpl']['dir_tmpl_administration'] . "ipblock.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$group_list = new Cgroups("mail_list", null);
$page->add($group_list);

$page->add(new CAdminPageMenuBlock());

include("../_include/core/administration_close.php");
