<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminFlashchat extends CHtmlBlock
{
	
	var $message = "";
	
	function action()
	{
		global $g;
		global $pay;
		global $l;		
		$cmd = get_param("cmd", "");
		
		if ($cmd == "update")
		{
			#DB::execute("TRUNCATE TABLE flashchat_rooms");
			
			$name = get_param_array("name");
			$status = get_param_array("status");
			
			foreach ($status as $k => $v)
			{
				$to_sql = "";
				if ($k > 0)
				{
					$to_sql = "id=" . to_sql($k) . "";
					if ($status[$k] == 0) DB::execute("DELETE FROM flashchat_users WHERE " . $to_sql . "");
					else DB::execute("UPDATE flashchat_users SET  status=" . to_sql($status[$k] * 3600) . " WHERE " . $to_sql . "");
				}
				else
				{
					if ($status[$k] > 0)
					{
						$id = DB::result("SELECT id FROM flashchat_users WHERE login=" . to_sql($name[$k]) . "");
						if ($id > 0) DB::execute("UPDATE flashchat_users SET status=" . to_sql($status[$k] * 3600) . " WHERE login=" . to_sql($name[$k]) . "");
						else DB::execute("INSERT INTO flashchat_users SET status=" . to_sql($status[$k] * 3600) . ", login=" . to_sql($name[$k]) . "");
					}
				}
			}
			
			redirect("flashchat_ban.php?action=saved");
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $pay;
		global $p;
		
		$html->setvar("message", $this->message);

		DB::query("SELECT * FROM flashchat_users WHERE status>0");
		while ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v) $html->setvar($k, $v);
			$html->setvar("status", ceil($row['status'] / 3600));
			$html->parse("item", true);
		}
		
		parent::parseBlock($html);
	}
}

$page = new CAdminFlashchat("", $g['tmpl']['dir_tmpl_administration'] . "flashchat_ban.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
