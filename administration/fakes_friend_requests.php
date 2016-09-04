<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminFriendRequests extends CHtmlList
{
	var $message;

	function action()
	{
		global $g_user;

		$cmd = get_param('cmd');
		$userTo = intval(get_param('user_friend'));
		$userFrom = intval(get_param('user_id'));

		if($cmd == 'remove_friend') {
			if ($userTo && $userFrom) {
                                User::friendDecline($userFrom, $userTo);                                

                                $sql = 'UPDATE user SET last_visit = NOW()WHERE user_id = ' . to_sql($userTo);
                                DB::execute($sql);

				redirect();
			}
		}
		if($cmd == 'approve_friend') {
			if ($userTo && $userFrom) {
                                User::friendApprove($userFrom, $userTo);                                

                                $sql = 'UPDATE user SET last_visit = NOW()  WHERE user_id = ' . to_sql($userTo);
                                DB::execute($sql);

				redirect();
			}
		}
	}

	function init()
	{
		parent::init();

		$this->m_on_page = 20;
		$this->m_sql_count = "select count(M.user_id) from friends_requests as M
		join user as U on U.user_id = M.user_id
		join user as U2 on U2.user_id = M.friend_id
		";

		$this->m_sql = "select M.*, U.name as u_from,
		U.use_as_online AS u_from_fake, U.register AS u_from_register,
		U2.name as u_to,
		U2.use_as_online AS u_to_fake, U2.register AS u_to_register
		FROM friends_requests as M
		JOIN user as U on U.user_id = M.user_id
		JOIN user as U2 on U2.user_id = M.friend_id
		";

		$this->m_sql_where = " (U2.use_as_online = 1 AND accepted=0) ";
		$this->m_sql_order = " created_at DESC ";
		//$this->m_field['id'] = array("id", null);
		$this->m_field['u_from'] = array("u_from", null);
		$this->m_field['u_to'] = array("u_to", null);

	}
	function onItem(&$html, $row, $i, $last)
	{
		$html->setvar('id', 0);
		$html->setvar('user_to', $row['friend_id']);
		$html->setvar('user_from', $row['user_id']);

		if($row['u_from_fake'] == 1) {
			$html->setblockvar('reply_action', '');
		} else {
			$html->parse('reply_action', false);
		}

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }

	}
	function parseBlock(&$html)
	{
        $html->setvar('message', $this->message);

		parent::parseBlock($html);
	}
}


$page = new CAdminFriendRequests('main', $g['tmpl']['dir_tmpl_administration'] . "fakes_friend_requests.html");

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuFakes());

include("../_include/core/administration_close.php");