<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
include("../_include/current/adv.class.php");

class CAdminLogin extends CHtmlBlock
{
	function action()
	{
		global $g;
		$del = get_param("delete", 0);
		$cmd = get_param("cmd","");
        $cat_id = get_param("cat_id",0);

		if($del!=0)
		{
			/*DB::query("select * from adv_razd where id=".$del);
			$row = DB::fetch_row();
			$cat_id = $row['cat_id'];
			DB::query("select * from adv_cats where id=".$cat_id);
			$row = DB::fetch_row();
			$name=$row['eng'];
			DB::execute("delete from adv_".$name." where razd_id=".$del);
			DB::execute("delete from adv_razd where id=".$del);

			redirect("adv_subcats.php?id=".$cat_id);*/
            CAdvTools::deleteSubcats($del);
            redirect("adv_subcats.php?id=".$cat_id);
		}
		if($cmd == "add")
		{
			$name = get_param("name","");
			if($name!="")
			{
				DB::execute("INSERT INTO adv_razd SET name=" . to_sql($name) . ", cat_id=" . to_sql($cat_id, 'Number'));
			}
			redirect("adv_subcats.php?id=".$cat_id);

		}

	}
	function parseBlock(&$html)
	{
		$cat_id = intval(get_param('id', 0));

        $html->setvar("cat_id",$cat_id);

		DB::query("select * from adv_razd where cat_id=".$cat_id);
		$i=1;
		while ($row = DB::fetch_row())
		{
            $lang = loadLanguageAdmin();
			if ($i % 2 == 0) {
				$html->setvar("class", 'color');
				$html->setvar("decl", '_l');
				$html->setvar("decr", '_r');
			  } else {
				$html->setvar("class", '');
				$html->setvar("decl", '');
				$html->setvar("decr", '');
			}
			$html->setvar("var_name",l($row['name'],$lang));
			$html->setvar("var_id",$row['id']);
			$html->parse("sub_item",true);
			$i++;
		}

		parent::parseBlock($html);
	}
}

$page = new CAdminLogin("", $g['tmpl']['dir_tmpl_administration'] . "adv_subcats.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
