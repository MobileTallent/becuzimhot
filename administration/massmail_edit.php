<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminMail extends CHtmlBlock
{
	var $message_massmail = "";
	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "add_file")
		{
			$name = "file";
			if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]["tmp_name"]))
			{
				$content = file_get_contents($_FILES[$name]["tmp_name"]);
				$mail = explode("\r\n", $content);
				foreach ($mail as $k => $v)
				{
					$v = substr($v, 0, 100);
					$id = DB::result("SELECT id FROM email WHERE mail=" . to_sql(trim($v), "Text") . "");

					if ($id == "0" and trim($v) != "")
					{
						DB::execute("INSERT INTO email (mail) VALUES(" . to_sql(trim($v), "Text") . ")");
					}
				}
				$this->message = "E-mail's added to database.";
			}
		}
		elseif ($cmd == "delete_file")
		{
			$name = "file";
			if (is_uploaded_file($_FILES[$name]["tmp_name"]))
			{
				$content = file_get_contents($_FILES[$name]["tmp_name"]);
				$mail = explode("\r\n", $content);
				foreach ($mail as $k => $v)
				{
					$v = substr($v, 0, 100);
					$mail = get_param("mail", "");
					$id = DB::result("SELECT id FROM email WHERE mail=" . to_sql(trim($v), "Text") . "");

					if ($id != "0" and trim($v) != "")
					{
						DB::execute("DELETE FROM email WHERE mail=" . to_sql(trim($v), "Text") . "");
					}
				}
				$this->message_massmail = "E-mail's deleted from database.";
			}
		}
		elseif ($cmd == "add")
		{
			$mail = get_param("mail", "");
			$id = DB::result("SELECT id FROM email WHERE mail=" . to_sql($mail, "Text") . "");

			if ($id == "0")
			{
				DB::execute("INSERT INTO email (mail) VALUES(" . to_sql($mail, "Text") . ")");
				$this->message_massmail = "E-mail added to database.";
			}
			else
			{
				$this->message_massmail = "This e-mail already exists in database.";
			}
		}
		elseif ($cmd == "delete")
		{
			$mail = get_param("mail", "");
			$id = DB::result("SELECT id FROM email WHERE mail=" . to_sql($mail, "Text") . "");

			if ($id != "0")
			{
				DB::execute("DELETE FROM email WHERE mail=" . to_sql($mail, "Text") . "");
				$this->message_massmail = "E-mail deleted from database.";
			}
			else
			{
				$this->message_massmail = "This e-mail absent in database.";
			}
		}
		elseif ($cmd == "delete_id")
		{
			$id = get_param("id", "");
			$id = DB::result("SELECT id FROM email WHERE id=" . to_sql($id, "Text") . "");

			if ($id != "0")
			{
				DB::execute("DELETE FROM email WHERE id=" . to_sql($id, "Text") . "");
				$this->message_massmail = "E-mail deleted from database.";
			}
			else
			{
				$this->message_massmail = "This e-mail absent in database.";
			}
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $p;
		$html->setvar("message_massmail", $this->message_massmail);

		parent::parseBlock($html);
	}
}

class CMails extends CHtmlList
{
	function init()
	{
		$this->m_sql_count = "SELECT COUNT(id) FROM email";
		$this->m_sql = "SELECT id, mail FROM email";

		$where = "";

		$like = get_param("like", "all");
		if ($like == "*")
		{
			for ($i = 0; $i <=9; $i++)
			{
				$where .= " OR mail LIKE '" . $i . "%'";
			}
			$where = " AND (" . substr($where, 4) .")";
		}
		elseif ($like != "all")
		{
			$where .= " AND mail LIKE '" . $like . "%'";
		}

		$domain = get_param("d", "");
		if ($domain != "")
		{
			$where .= " AND mail LIKE '%" . $domain . "'";
		}

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = " mail";
		$this->m_sql_from_add = "";

		$this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_field['id'] = array("id", null);
		$this->m_field['mail'] = array("mail", null);
	}
	function parseBlock(&$html)
	{
		$abc = explode(" ", "All * A B C D E F G H I J K L M N O P Q R S T U V W X Y Z");
		foreach ($abc as $k => $v)
		{
			$html->setvar("like", strtolower($v));
			$html->setvar("like_title", ucfirst($v));
			$html->parse("like", true);
		}

		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		DB::query("SELECT user_id, name FROM user WHERE mail=" . to_sql($row['mail'], "Text") . "", 2);
		if ($row2 = DB::fetch_row(2))
		{
			$html->setvar("user_id", $row2['user_id']);
			$html->setvar("user_name", $row2['name']);
			$html->parse("user", false);
			$html->setblockvar("nouser", "");
		}
		else
		{
			$html->parse("nouser", false);
			$html->setblockvar("user", "");
		}

		if (strstr($_SERVER['HTTP_HOST'], "abk") and !strstr($_SERVER['PHP_SELF'], "dev")) {
			$this->m_field['mail'][1] = 'disabled@ondemoadmin.cp';
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

		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CAdminMail("", $g['tmpl']['dir_tmpl_administration'] . "massmail_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$mails = new CMails("mails", null);
$page->add($mails);

include("../_include/core/administration_close.php");

?>
