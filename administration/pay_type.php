<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminPay extends CHtmlBlock
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
			DB::execute("TRUNCATE TABLE payment_type");

			$gold = get_param_array("gold");
			$silver = get_param_array("silver");
			$platinum = get_param_array("platinum");

			foreach ($gold as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='gold'");
			foreach ($silver as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='silver'");
			foreach ($platinum as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='platinum'");

			global $p;
			redirect($p."?action=saved");

		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $pay;
		global $p;


		DB::query('SELECT * FROM payment_cat ORDER BY code ASC');
		while ($row = DB::fetch_row()) {
			$html->setvar('item', $row['id']);
			$html->setvar('pay_name', $row['name']);
			$html->setvar('pay_code', $row['code']);

			$check = DB::result("select code from payment_type where type='silver' and code=".to_sql($row['code']),0,2);
			if(empty($check)) $html->setvar("silver_checked", '');
			else $html->setvar("silver_checked", 'checked');

			$check = DB::result("select code from payment_type where type='gold' and code=".to_sql($row['code']),0,2);
			if(empty($check)) $html->setvar("gold_checked", '');
			else $html->setvar("gold_checked", 'checked');

			$check = DB::result("select code from payment_type where type='platinum' and code=".to_sql($row['code']),0,2);
			if(empty($check)) $html->setvar("platinum_checked", '');
			else $html->setvar("platinum_checked", 'checked');

			$html->parse("item", true);
		}

		parent::parseBlock($html);
	}
}

$page = new CAdminPay("", $g['tmpl']['dir_tmpl_administration'] . "pay_type.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include("../_include/core/administration_close.php");

?>
