<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminReplier extends CHtmlBlock
{

	var $message = "";
    var $table = 'admin_replier';

	function action()
	{
		global $g;
		global $pay;
		global $l;
		global $p;

		$cmd = get_param('cmd', '');

        $username = trim(get_param('username'));
        $id = trim(get_param('id'));
        $password = trim(get_param('password'));

		if ($cmd == 'update') {
            $chngPass='';
            if($password!=''){
                $chngPass=', password='.to_sql(md5($password), 'Text').' ';
            }
            $sql = 'UPDATE ' . $this->table . '
                SET username = ' . to_sql($username, 'Text') . $chngPass.'
                    WHERE id = ' . to_sql($id, 'Number');
			DB::execute($sql);
		}

		if($cmd == 'delete' ) {
            $sql = 'DELETE FROM ' . $this->table . '
                WHERE id = ' . to_sql($id);
            DB::execute($sql);
        }

		if($cmd == 'add' ) {
            if($username!='admin' && $username!=''){
                $sql = 'INSERT INTO ' . $this->table . '
                    SET username = ' . to_sql($username, 'Text') . ',
                        password = ' . to_sql(md5($password), 'Text').'';
                DB::execute($sql);
            } else {
                $this->message=l('incorrect_username');
            }    
        }

        if($cmd && $this->message=='') {
            redirect($p . '?action=saved');
        }
	}

	function parseBlock(&$html)
	{
		$html->setvar("message", $this->message);

		parent::parseBlock($html);
	}
}

class Cgroups extends CHtmlList
{
	function init()
	{
		parent::init();
		$this->m_on_page = 20;
		$this->m_sql_count = 'select count(*) from admin_replier';
		$this->m_sql = 'select * from admin_replier';

		$this->m_sql_order = ' username ASC ';
		$this->m_field['id'] = array('id', null);
		$this->m_field['username'] = array('username', null);
		$this->m_field['password'] = array('password', null);
	}

	function onItem(&$html, $row, $i, $last)
	{
        $this->m_field['password'][1] = htmlentities($row['password'], ENT_QUOTES, 'UTF-8');
	}
}

$page = new CAdminReplier("", $g['tmpl']['dir_tmpl_administration'] . "users_replier.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$page->add(new CAdminPageMenuFakes());

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$group_list = new Cgroups("mail_list", null);
$page->add($group_list);

include("../_include/core/administration_close.php");
