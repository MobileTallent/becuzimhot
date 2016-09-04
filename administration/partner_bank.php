<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g_options;
		$cmd = get_param("cmd", "");

		if ($cmd == "delete")
		{
			DB::execute("
				DELETE FROM partner WHERE
				partner_id=" . to_sql(get_param("id", ""), "Number") . "
			");
		}
	}

	function init()
	{
		$cmd = get_param("cmd", "");

		if ($cmd == "edit")
		{
			#if ($this->jmessage == "")
			{
				DB::execute("
					UPDATE partner
					SET
					bank_name=" . to_sql(get_param("bank_name", ""), "Text") . ",
					bank_phone=" . to_sql(get_param("bank_phone", ""), "Text") . ",
					bank_adress1=" . to_sql(get_param("bank_adress1", ""), "Text") . ",
					bank_adress2=" . to_sql(get_param("bank_adress2", ""), "Text") . ",
					bank_city=" . to_sql(get_param("bank_city", ""), "Text") . ",
					bank_state=" . to_sql(get_param("bank_state", ""), "Text") . ",
					bank_zip=" . to_sql(get_param("bank_zip", ""), "Text") . ",
					bank_country=" . to_sql(get_param("bank_country", ""), "Text") . ",
					bank_account=" . to_sql(get_param("bank_account", ""), "Text") . ",
					bank_aba=" . to_sql(get_param("bank_aba", ""), "Text") . ",
					bank_swift=" . to_sql(get_param("bank_swift", ""), "Text") . ",
					bank_type=" . to_sql(get_param("bank_type", ""), "Text") . ",
					bank_to=" . to_sql(get_param("bank_to", ""), "Text") . ",
					paypal=" . to_sql(get_param("paypal", ""), "Text") . ",
					other=" . to_sql(get_param("other", ""), "Text") . "
					WHERE partner_id=" . to_sql(get_param("id", ""), "Number") . "
				");

				redirect("partner_bank.php?id=".get_param("id", "")."&action=saved");

			}
		}
	}

	function parseBlock(&$html)
	{
		global $g_options;

		$html->setvar("message", $this->message);

		DB::query("SELECT * FROM partner WHERE partner_id=" . to_sql(get_param("id", ""), "Number") . " ORDER BY partner_id");
		if ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v)
			{
				$html->setvar($k, get_param($k, he($v)));
			}
		}

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partner_bank.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
