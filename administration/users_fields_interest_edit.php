<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include("../_include/core/administration_start.php");

class CInterestEdit extends CHtmlBlock
{
	function action()
	{
        global $g;

		$cmd = get_param('cmd');
		if ($cmd == 'update') {
            $params = get_params_string();
            $id = get_param('id');
            $interest = trim(get_param('interest'));
            if ($id && $interest) {
                DB::update('interests', array('interest' => $interest),'`id` = ' . to_sql($id));
            }
			redirect("users_fields_interests.php?{$params}");
		}
	}

	function parseBlock(&$html)
	{
        $id = get_param('id');
        if ($id) {
            $html->setvar('id', $id);
            $interest = DB::result('SELECT `interest` FROM `interests` WHERE `id` = ' . to_sql($id));
            if ($interest) {
                $html->setvar('interest', he($interest));
            }
            $params = get_params_string();
            $params = del_param('id', $params);
            $html->setvar('params', $params);
        }
		parent::parseBlock($html);
	}
}

$page = new CInterestEdit('', $g['tmpl']['dir_tmpl_administration'] . 'users_fields_interest_edit.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

global $p;
$p = 'users_fields_interests.php';
$page->add(new CAdminPageMenuUsersFields());

include('../_include/core/administration_close.php');