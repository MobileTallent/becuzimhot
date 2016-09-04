<?php
/* (C) ABK-Soft Ltd., 2004-2006
IMPORTANT: This is a commercial software product
and any kind of using it must agree to the ABK-Soft
Ltd. license agreement.
It can be found at http://abk-soft.com/license.doc
This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");

class FakesReplyMails extends CHtmlList
{
	var $message;

	function action()
	{
		global $g, $g_user, $p;

		$action = get_param('action');
		$cmd = get_param('cmd');
		$id = get_param('id');
		$userTo = intval(get_param('user_to'));
		$userFrom = intval(get_param('user_from'));
		$subject = trim(get_param('subject'));
		$text = trim(get_param('text'));

        if($cmd == 'lang') {
            header('Content-Type: text/xml; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');
            $words = array(
                'loading',
                'background',
                'object'
            );
            $lang = '<lang>';
            foreach($words as $wordKey) {
                $lang .= "<word name='$wordKey'>" . l($wordKey,false) . '</word>';
            }
            $lang .= '</lang>';
            echo $lang;
            die();
        }

		if($cmd == 'reply') {
			// reply - check block list!!!
			/*if ($userTo && $userFrom && $subject != '' && $text != '') {
				$id = $userTo;
				$g_user['user_id'] = $userFrom;
				$g_user['name'] = DB::result('SELECT name FROM user WHERE user_id = ' . $userFrom);

				$sql = "SELECT id FROM users_block WHERE user_from=" . $id . " AND user_to=" . $g_user['user_id'];
				$block = DB::result($sql);

				if ($block == 0) {

					$subject = to_sql($subject, 'Text');
					$text = to_sql($text, 'Text');
                    $idMailFrom = 0;

					DB::execute("
					INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, type, receiver_read)
						VALUES(
						" . to_sql($id, "Number") . ",
						" . $g_user['user_id'] . ",
						" . to_sql($id, "Number") . ",
						" . 1 . ",
						" . $subject . ",
						" . $text . ",
						" . time() . ",
						" . to_sql(get_param('type')) . ",
                        'N')
					");
                    $idMailFrom = DB::insert_id();

					DB::execute("UPDATE user SET last_visit=last_visit, new_mails=new_mails+1 WHERE user_id=" . to_sql($id, "Number") . "");

					$sql = 'UPDATE user SET last_visit = NOW()
					WHERE user_id = ' . $g_user['user_id'];
					DB::execute($sql);

					// save copy
					if (true) {
						DB::execute("
							INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, new, type, receiver_read, sent_id)
							VALUES(
							" . $g_user['user_id'] . ",
							" . $g_user['user_id'] . ",
							" . to_sql($id, "Number") . ",
							" . 3 . ",
							" . $subject . ",
							" . $text . ",
							" . time() . ",
							'N',
							" . to_sql(get_param('type')) . ",
                            'N',
                            " . to_sql($idMailFrom, 'Number') . ")
						");
					}

					DB::query("SELECT name, orientation, mail, set_email_mail FROM user WHERE user_id='" . $id . "'");
					if ($row = DB::fetch_row()) {
						if ($row['set_email_mail'] != "2") {
							$subject = DB::result("SELECT subject FROM email_auto WHERE note='mail_message'");
							$subject = str_replace("{name}", $g_user['name'], $subject);
							$subject = str_replace("{title}", $g['main']['title'], $subject);
							$text = DB::result("SELECT text FROM email_auto WHERE note='mail_message'");
							$text = str_replace("{name}", $g_user['name'], $text);
							$text = str_replace("{title}", $g['main']['title'], $text);

							send_mail(
								$row['mail'],
								$g['main']['info_mail'],
								$subject,
								$text
							);
						}
					}
					redirect();
				} elseif ($block > 0) {
					$toName = DB::result('SELECT name FROM user WHERE user_id = ' . $id);
					$this->message = $g_user['name'] . " in Block List of " . $toName . "<br>";
				} else {
					$this->message = "Incorrect Username.<br>";
				}
			} else {
				$this->message = "Empty username, subject or message.<br>";
			}*/
            $this->message = Common::sendMail(null, true);
            if ($this->message == '') {
                redirect(Common::urlPage());
            }
		}
	}

	function init()
	{
		parent::init();
		global $g;
		global $g_user;
		$user_id = get_param('user_id');
		$this->m_on_page = 10;
		$this->m_sql_count = "select count(M.id) from mail_msg as M join user as U on U.user_id = M.user_from
		join user as U2 on U2.user_id = M.user_to
		JOIN user as FAKE ON FAKE.user_id = M.user_id
		";

		$this->m_sql = "select M.*, IF(M.receiver_read = 'N' OR M.receiver_read = '', 'Y', 'N') as new_mail, U.name as u_from,
		U2.name as u_to,
		U.user_id AS u_from_id,
        U.name AS u_from_name,
		U2.user_id AS u_to_id,
		FAKE.user_id AS fake_user_id
		FROM mail_msg as M
		JOIN user as U on U.user_id = M.user_from
		JOIN user as U2 on U2.user_id = M.user_to
		JOIN user as FAKE ON FAKE.user_id = M.user_id
		";

		$this->m_sql_where = " FAKE.use_as_online = 1
            AND (M.folder = 1 OR M.folder = 3)
            AND M.user_id = FAKE.user_id
            AND (U.use_as_online = 0 OR U2.use_as_online = 0)";
		$this->m_sql_order = " M.id DESC  ";
		$this->m_field['id'] = array("id", null);
		$this->m_field['u_from'] = array("u_from", null);
		$this->m_field['u_from_id'] = array("u_from_id", null);
		$this->m_field['u_to'] = array("u_to", null);
		$this->m_field['u_to_id'] = array("u_to_id", null);
		$this->m_field['subject'] = array("subject", null);
		$this->m_field['text'] = array("text", null);
		$this->m_field['date_sent'] = array("date_sent", null);

	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

		$html->setvar("id", $row['id']);
		$html->setvar("user_to", $row['u_to']);
		$html->setvar("user_from", $row['u_from']);
		$html->setvar("u_from_id", $row['u_from_id']);
        $html->setvar("u_from_name", $row['u_from_name']);
        $html->setvar("u_to_id", $row['u_to_id']);

		$real_text = Common::parseLinksTag($row['text'], 'a', '<', 'parseLinks', '_blank', '', true);
        $row['subject'] = strip_tags($row['subject']);
		$row['text'] = strip_tags($row['text']);

        $html->setvar('subject', he(mail_chain($row['subject'])));
        if ($row['type'] == 'postcard') {
            $param = array('tz', 'ty', 'tx', 't', 'snd', 'py', 'px', 'pers', 'bg', 'rec_id', 'id');
            $get = explode('|', $row['text']);
            $getPostcard = '';
            for ($i = 0; $i < count($param); $i++) {
                if ($param[$i] == 't') {
                    $get[$i] = urlencode($get[$i]);
                }
                $getPostcard .= "&{$param[$i]}={$get[$i]}";
            }
            $html->setvar('get_postcard', mb_substr($getPostcard, 1, mb_strlen($getPostcard, 'UTF-8'), 'UTF-8'));
            $html->parse('show_postcard', false);
            $html->setvar('text_mail', '');
            $html->setvar('text_reply', '');
        } else {
            $html->setvar('text_mail', nl2br(strip_tags($real_text, '<a><br>')));
            $text = str_replace("\n", "\n> ", $row['text']);
            $text = str_replace("\n> >","\n>>",$text);
            if ($row['type'] == 'plain' or $row['type'] == '') {
                $text = "\n\n\n> " . $text;
            }
            $html->setvar("text_reply", $text);
            $html->setblockvar('show_postcard', '');
        }

		$html->setvar("date", date('d-m-Y H:i:s',$row['date_sent']));

		// parse reply for fakes
		if($row['fake_user_id'] != $row['u_from_id']) {
            if (User::isBlocked('mail', $row['u_from_id'], $row['fake_user_id']) == 0) {
                $html->parse('reply_subject', false);
                $html->setblockvar('reply_block', '');
            } else {
                $html->setblockvar('reply_subject', '');
                $html->parse('reply_block', false);
            }
			//$html->parse('reply_text', false);
			//$html->parse('reply_action', false);
		} else {
            $html->setblockvar('reply_subject', '');
			$html->setblockvar('reply_block', '');
			//$html->setblockvar('reply_text', '');
			//$html->setblockvar('reply_action', '');
		}

        if ($row['new_mail'] == 'Y') {
			$html->setblockvar('mail_old', '');
			$html->parse('mail_new', false);
		} else {
			$html->setblockvar('mail_new', '');
			$html->parse('mail_old', false);
		}

        if ($row['fake_user_id'] != $row['u_from_id']) {
            //echo $row['fake_user_id'] . '-' . $row['u_from_id'] . '-' . $row['u_to_id'] . '-' . $row['id'] . '-' . $row['sent_id'] . '-' .$row['new_mail'] .'<br>';
            $sql = "UPDATE `mail_msg`
                       SET new = 'N'
                     WHERE `user_id` = " . to_sql($row['fake_user_id'], 'Number') . "
                       AND id = " . to_sql($row['id'], 'Number');
            DB::execute($sql);
            if ($row['new_mail'] == 'Y')
                DB::execute("UPDATE `mail_msg` SET `receiver_read` = 'Y' WHERE user_id = " . to_sql($row['fake_user_id'], 'Number') . " AND id = " . to_sql($row['id'], 'Number'));
            if ($row['sent_id'] != 0)
                DB::execute("UPDATE `mail_msg` SET `receiver_read` = 'Y' WHERE id = " . to_sql($row['sent_id'], 'Number'));
        }

	}
	function parseBlock(&$html)
	{
        $html->setvar('message', $this->message);

		parent::parseBlock($html);
	}
}


$page = new FakesReplyMails("mail_list", $g['tmpl']['dir_tmpl_administration'] . "fakes_reply_mails.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuFakes());

include("../_include/core/administration_close.php");