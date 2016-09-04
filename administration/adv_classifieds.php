<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
include("../_include/current/adv.class.php");

class CClassifieds extends CHtmlList
{
	function action()
	{
        $del = intval(get_param('delete', 0));
		if($del != 0)
		{
            $cat_id = intval(get_param('id', 0));
            CAdvTools::deleteAdv(null, $del, $cat_id, true);

            redirect('adv_classifieds.php?id=' . $cat_id);
		}

	}

	function init()
	{
		global $g;

		$this->m_on_page = 10;
		$this->m_on_bar = 10;

		$cat_id = intval(get_param("id",0));

		DB::query("select * from adv_cats where id=".$cat_id);
		$row = DB::fetch_row();
        if(!$row) {
            redirect('adv.php');
        }
		$cat_name = $row['eng'];
		$this->m_sql_count = "SELECT COUNT(id) FROM adv_".$cat_name ." ". $this->m_sql_from_add;
		$this->m_sql = "SELECT * FROM adv_".$cat_name ."  ". $this->m_sql_from_add;
		$this->m_sql_where = "1";
		$this->m_sql_order = "id";

                $this->m_field['id'] = array("id", null);
                $this->m_field['subject'] = array("subject", null);
                $this->m_field['body'] = array("body", null);
		$this->m_field['razd_id'] = array("razd_id", null);

	}

	function parseBlock(&$html)
	{
		$cat_id = get_param("id",0);
		$html->setvar("cat_id",$cat_id);
		parent::parseBlock($html);
	}

	function onItem(&$html, $row, $i, $last)
	{
		#$this->m_field['subject'][1] = "Subject";
		$this->m_field['razd_id'][1]=DB::result("select name from adv_razd where id=".$row['razd_id'],0,2);
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

$page = new CClassifieds("main", $g['tmpl']['dir_tmpl_administration'] . "adv_classifieds.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
