<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminAdvEdit extends CHtmlBlock
{

	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");
		if($cmd == "edit")
		{
			$id=get_param("id",0);
			$cat_id=get_param("cat",0);
			$cat_name=DB::result("select eng from adv_cats where id =".$cat_id);
			$subject = get_param("subject","");
			if($subject!="")
			{
				DB::execute("update adv_".$cat_name." set subject='".addslashes($subject)."', razd_id = ".addslashes(get_param("subcategory",0)).", body = '".addslashes(get_param("body",""))."'  where id =".$id);
			}
			redirect("adv_classifieds.php?action=saved&id=".$cat_id);
		 }
	}
			
	function parseBlock(&$html)
	{
		$id = get_param("id",0);
		$html->setvar("id",$id);
		$cat_id = get_param("cat",0);
		$html->setvar("cat",$cat_id);
		$cat_name=DB::result("select eng from adv_cats where id =".$cat_id);
		DB::query("select * from adv_".$cat_name." where id =".$id);
		$row = DB::fetch_row();
		$html->setvar("subject",he($row['subject']));
		DB::query(" select * from adv_razd where cat_id=".$cat_id);
		$subcats = DB::db_options("SELECT id AS id, name AS title FROM adv_razd where cat_id =".$cat_id, $row['razd_id']);
		$html->setvar("subcats",$subcats);
		$html->setvar("body",$row['body']);
		parent::parseBlock($html);
	}

}

$page = new CAdminAdvEdit("main", $g['tmpl']['dir_tmpl_administration'] . "adv_classifieds_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
