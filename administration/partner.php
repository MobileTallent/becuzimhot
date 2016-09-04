<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlList
{

	var $message = "";
	var $login = "";
	var $m_on_page = 20;

	function init()
	{
		$this->m_sql_count = "SELECT COUNT(partner_id) FROM partner";
		$this->m_sql = "SELECT * FROM partner";
		$this->m_sql_where = "1";
		$this->m_sql_order = "partner_id";

		$this->m_field['partner_id'] = array("partner_id", null);
		$this->m_field['count_users'] = array("count_users", null);
		$this->m_field['count_golds'] = array("count_golds", null);
		$this->m_field['count_refs'] = array("count_refs", null);
		$this->m_field['name'] = array("name", null);
		$this->m_field['mail'] = array("mail", null);
		$this->m_field['domain'] = array("domain", null);
		$this->m_field['payment'] = array("payment", null);
		$this->m_field['payment_last'] = array("payment_last", null);
		$this->m_field['payment_last_date'] = array("payment_last_date", null);
		$this->m_field['account'] = array("account", null);
	}

	function action()
	{
		global $g_options;
		$cmd = get_param("cmd", "");

		if ($cmd == "delete")
		{
            $id = get_param('id');
			DB::query('SELECT * FROM partner WHERE partner_id = ' . to_sql($id, 'Number'));
			$row = DB::fetch_row();

            if (Common::isEnabledAutoMail('partner_delete')) {
                $vars = array(
                    'title' => Common::getOption('title', 'main'),
                    'name' => $row['name'],
                );
                Common::sendAutomail($row['lang'], $row['mail'], 'partner_delete', $vars);
            }

			DB::execute('DELETE FROM partner WHERE partner_id = ' . to_sql($id, 'Number'));
		}
		if ($cmd == "payment")
		{
			DB::query("SELECT * FROM partner WHERE	partner_id=" . to_sql(get_param("id", ""), "Number") . "");
			$row = DB::fetch_row();

			if ($row['payment'] > 0) {
				$sql = "
					UPDATE partner
					SET payment_last=payment, account=(account-payment), payment=0, payment_last_date = NOW()
					WHERE partner_id=" . to_sql(get_param("id", ""), "Number") . "
				";
				DB::execute($sql);

				global $p;
				redirect("$p?action=saved");
			}

		}
	}

	function parseBlock(&$html)
	{
		$html->setvar("page_url", "administration/partner.php");
		parent::parseBlock($html);
	}

	function onItem(&$html, $row, $i, $last)
	{
		if ($row['payment'] > 0)
		{
			$this->m_field['payment'][1] = $row['payment'];
			$cp = true;
		}
		else
		{
			$this->m_field['payment'][1] = " ";
			$cp = false;
		}

$html->subcond($cp, 'can_payment');
$html->subcond(!$cp, 'no_payment');

		if ($row['payment_last'] == 0)
		{
			$this->m_field['payment_last'][1] = " ";
		}
		else $this->m_field['payment_last'][1] = $row['payment_last'];

		if ($row['payment_last_date'] == "0000-00-00 00:00:00")
		{
			$this->m_field['payment_last_date'][1] = " ";
		}
		else $this->m_field['payment_last_date'][1] = $row['payment_last_date'];

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
}

$page = new CForm("partner", $g['tmpl']['dir_tmpl_administration'] . "partner.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
