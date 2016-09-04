<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminLogin extends CHtmlBlock
{
	function action()
	{
		$cmd = get_param("cmd", "");
		if($cmd == "edit")
		{
			$id = get_param("id",0);
			if($id!=0)
			{
				$name = get_param("name","");
				$id = get_param("id",0);
				if(($name!="")&&($id!=0)) DB::execute("UPDATE adv_razd SET name=" . to_sql($name) . " WHERE id=" . to_sql($id, 'Number'));
				DB::query("select * from adv_razd where id=".$id);
				$row = DB::fetch_row();
				redirect("adv_subcats.php?action=saved&id=".$row['cat_id']);
			}
		}

	}
	function parseBlock(&$html)
	{
		$sub_id = get_param("id",0);
		if($sub_id!=0)
		{
			DB::query("select * from adv_razd where id=".$sub_id);
			$row=DB::fetch_row();
			$html->setvar("id",$row['id']);
			$html->setvar("name", he($row['name']));
			parent::parseBlock($html);
		} else redirect("adv.php");
	}
}

$page = new CAdminLogin("", $g['tmpl']['dir_tmpl_administration'] . "adv_subcat_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
