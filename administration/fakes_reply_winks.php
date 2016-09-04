<?php
/* (C) ABK-Soft Ltd., 2004-2006
IMPORTANT: This is a commercial software product
and any kind of using it must agree to the ABK-Soft
Ltd. license agreement.
It can be found at http://abk-soft.com/license.doc
This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");

class FakesReplyWinks extends CHtmlList
{
	var $message;

	function action()
	{
		global $g_user;

		$cmd = get_param('cmd');
		$userTo = intval(get_param('user_to'));
		$userFrom = intval(get_param('user_from'));

		if($cmd == 'reply') {
			// reply - check block list!!!
			if ($userTo && $userFrom) {
				$g_user = User::getInfoBasic($userFrom);
                Common::sendWink($userTo);

                $sql = 'UPDATE user SET last_visit = NOW()
                    WHERE user_id = ' . to_sql($userFrom);
                DB::execute($sql);

				redirect();
			}
		}
	}

	function init()
	{
		parent::init();

		$this->m_on_page = 20;
		$this->m_sql_count = "select count(M.id) from users_interest as M
		join user as U on U.user_id = M.user_from
		join user as U2 on U2.user_id = M.user_to
		";

		$this->m_sql = "select M.*, U.name as u_from,
		U.use_as_online AS u_from_fake, U.register AS u_from_register,
		U2.name as u_to,
		U2.use_as_online AS u_to_fake, U2.register AS u_to_register
		FROM users_interest as M
		JOIN user as U on U.user_id = M.user_from
		JOIN user as U2 on U2.user_id = M.user_to
		";

		$this->m_sql_where = " (U.use_as_online = 1 OR U2.use_as_online = 1) ";
		$this->m_sql_order = " id DESC ";
		$this->m_field['id'] = array("id", null);
		$this->m_field['u_from'] = array("u_from", null);
		$this->m_field['u_to'] = array("u_to", null);

	}
	function onItem(&$html, $row, $i, $last)
	{
		$html->setvar('id', $row['id']);
		$html->setvar('user_to', $row['user_to']);
		$html->setvar('user_from', $row['user_from']);

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


$page = new FakesReplyWinks('main', $g['tmpl']['dir_tmpl_administration'] . "fakes_reply_winks.html");

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuFakes());

include("../_include/core/administration_close.php");