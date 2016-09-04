<?php
/* (C) ABK-Soft Ltd., 2004-2006
IMPORTANT: This is a commercial software product
and any kind of using it must agree to the ABK-Soft
Ltd. license agreement.
It can be found at http://abk-soft.com/license.doc
This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class FakesReplyIm extends CHtmlList
{
    var $message = '';

	function action()
	{
		global $g;
		global $g_user;

		$cmd = get_param('cmd');
		$userTo = intval(get_param('user_to'));
		$userFrom = intval(get_param('user_from'));
		$text = trim(get_param('text'));

        if ($cmd == 'writing') {
            $writing = json_decode(get_param('writing'));
            foreach ($writing as $fromUser => $toUser) {
                $where = '`from_user` = ' . to_sql($fromUser, 'Number') .
                         ' AND `to_user` = ' . to_sql($toUser->user_to, 'Number');
                DB::update('im_open', array('last_writing' => $toUser->time), $where);
            }
            die();
        }elseif($cmd == 'reply') {
            // reply - check block list!!!
			// check open im
			if ($userTo && $userFrom && $text) {
                $id = $userTo;
                $old_g_user=$g_user;
                $g_user=User::getInfoBasic($userFrom);
//				$g_user['user_id'] = $userFrom;
//				$g_user['name'] = DB::result('SELECT name FROM user WHERE user_id = ' . $userFrom);

				$sql = "SELECT id FROM user_block_list WHERE im = 1 AND user_from=" . $id . " AND user_to=" . $g_user['user_id'];
				$block = DB::result($sql);

				if ($block == 0) {

					$msg = str_replace("<", "&lt;", $text);

					// OPEN IM WINDOW

                    /*$to_user = $id;

					$censured = false;

					$censuredFile = dirname(__FILE__) . "/../_server/im_new/feature/censured.php";
					if (file_exists($censuredFile)) {
						include($censuredFile);
					}*/
                    $msg = censured($msg);
					if ($msg) {
						/*$smiles = array(
							'1' => array(':-)', ':)', ':0)', ':o)', '))'), '2' => array('=-0', '=-o', '=-O', '8-0', '8-O', '8-o'), '3' => array(':-D', ':D', ':-d'),
							'4' => array(';-)', ';)', ';-D'), '5' => array(':\'-(', ':,-(', ':,('), '6' => array(':-(', ':(', '(('), '7' => array(':-*', ':*'), '8' => array('8-)'),
							'9' => array(':-/', ':/', ':-[', ':-\\', ':-|'), '10' => array(':-P', ':-p', ':P', ':p'),
						);
						foreach ($smiles as $smile_num => $smile_repls) {
							foreach ($smile_repls as $repl) {
								$msg = str_replace($repl, '<span class="smile sm' . $smile_num . '"><img src="_server/im_new/smiles/sm' . $smile_num . '.png" width="21" height="21" alt="" /></span>', $msg);
							}
						}*/



                        CIm::addMessageToDb($id, $msg);
/*
						$sql = "SELECT * FROM im_open
						WHERE to_user=" . to_sql($id, "Number") . "
						AND from_user=" . to_sql($g_user['user_id'], "Number");
						DB::query($sql);
						if (DB::num_rows() == 0) {
							$sql = "INSERT INTO im_open
							SET to_user=" . to_sql($id, "Number") . ",
							from_user=" . to_sql($g_user['user_id'], "Number");
							DB::execute($sql);
						}

						$sql = 'INSERT INTO im_msg
						SET from_user = ' . to_sql($g_user['user_id'], "Number") . ',
						to_user = ' . to_sql($id, "Number") . ',
						born = ' . to_sql(date('Y-m-d H:i:s'), 'Text') . ',
						name = ' . to_sql($g_user['name'], "Text") . ',
						msg = ' . to_sql($msg, "Text") . ',
						ip =  ' . to_sql($_SERVER['REMOTE_ADDR'], "Text") . '
						';
						DB::execute($sql);

						$sql = 'UPDATE user SET last_visit = NOW()
						WHERE user_id = ' . $g_user['user_id'];
						DB::execute($sql);
*/
						redirect();
					}
				} else {
					$toName = DB::result('SELECT name FROM user WHERE user_id = ' . $id);
					$this->message = $g_user['name'] . " in Block List of " . $toName . "<br>";
				}
                $g_user=$old_g_user;

			}
		}

	}

	function init()
	{
		parent::init();
		global $g;
		global $g_user;
		$user_id = get_param('user_id');

		$this->m_on_page = 20;
		$this->m_sql_count = "select count(M.id) from im_msg as M
		join user as U on U.user_id = M.from_user
		join user as U2 on U2.user_id = M.to_user
		";

		$this->m_sql = "select M.*, U.name as user_from,
		U.use_as_online AS u_from_fake, U.register AS u_from_register,
		U2.name as user_to,
		U2.use_as_online AS u_to_fake, U2.register AS u_to_register
		FROM im_msg as M
		JOIN user as U on U.user_id = M.from_user
		JOIN user as U2 on U2.user_id = M.to_user
		";

		$this->m_sql_where = " (U.use_as_online = 1
		OR U2.use_as_online = 1) ";
		$this->m_sql_order = " id DESC ";
		$this->m_field['id'] = array("id", null);
		$this->m_field['user_from'] = array("user_from", null);
		$this->m_field['user_to'] = array("user_to", null);
		$this->m_field['from_user'] = array("from_user", null);
		$this->m_field['to_user'] = array("to_user", null);
		$this->m_field['msg'] = array("msg", null);
		$this->m_field['born'] = array("born", null);

	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		$user_id = get_param('user_id');
		$html->setvar("id", $row['id']);

		$html->setvar("from_user", $row['from_user']);
		$html->setvar("to_user", $row['to_user']);


        $msg = Common::parseLinksTag(to_html($row['msg']), 'a', '&lt;', 'parseLinksSmile', '_blank', '', true);
        if($row['msg_translation']!=''){
            $msg =$msg. ' ('. Common::parseLinksTag(to_html($row['msg_translation']), 'a', '&lt;', 'parseLinksSmile', '_blank', '', true).') ';
        }
        $html->setvar('msg_im', $msg);
		// parse reply for fakes

		if ($row['u_from_fake'] == 1) {
            if (!$row['is_new']) {
               $html->parse('is_read', false);
            }
			$html->setblockvar('reply', '');
		} else {
            $sql = 'UPDATE `im_msg`
                       SET `is_new` = 0
                     WHERE `is_new` > 0
                       AND `to_user` = ' . to_sql($row['to_user'], 'Number');
            DB::execute($sql);
            $html->setblockvar('is_read', '');
			$html->parse('reply', false);
		}

	}


	function parseBlock(&$html)
	{
		$html->setvar('message', $this->message);

		parent::parseBlock($html);
	}
}


$page = new FakesReplyIm("mail_list", $g['tmpl']['dir_tmpl_administration'] . "fakes_reply_im.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuFakes());

include("../_include/core/administration_close.php");