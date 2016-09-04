<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdninAdv extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $DB_conn;
		global $db;
		$db = $DB_conn;
		$content = '';
		$inc_path = "";
		include("../_include/current/query.php");
		include("../_include/current/utils.php");
		include("../_include/current/table.php");
		$q = new query();
		if(IS_DEMO or empty($_GET['cat_id']))
		{
			DB::query("select * from adv_cats");
			$i=1;
			while ($row = DB::fetch_row())
			{
                $lang = loadLanguageAdmin();
				$col = DB::result("select count(id) as col from adv_razd where cat_id=".$row['id'], 0, 2);
				$col2 = DB::result("select count(id) as col from adv_".$row['eng']." where cat_id=".$row['id'], 0, 2);
				$html->setvar("var_name",l($row['name'],$lang));
				$html->setvar("var_id",$row['id']);
				$html->setvar("col",$col);
				$html->setvar("col2",$col2);
				if ($i % 2 == 0) {
					$html->setvar("class", 'color');
					$html->setvar("decl", '_l');
					$html->setvar("decr", '_r');
				  } else {
					$html->setvar("class", '');
					$html->setvar("decl", '');
					$html->setvar("decr", '');
				}
				$i++;
				$html->parse("adv_item",true);
			}
			$html->parse("adv", true);
		}
		else
		{
			DB::query("select * from adv_cats where id=".$_GET['cat_id']);
			$adv_cat = DB::fetch_row();
			$content .= '<h2>'.$adv_cat['name'].'</h2>';
			if($_GET['action']=='razd')
			{
				$table = new cTable('subcategories','adv_razd','id',$is_rank=0, $is_status=0, $is_add=1,$is_edit=1,$is_del=1);
				$table->settings('cat_id='.$_GET['cat_id']);
				$table->insertparam('cat_id', $_GET['cat_id']);
				$table->insertcol('Name','name',1,1,'text',$size='',$max='');
				ob_start();
				$table->draw();
				$content .= ob_get_clean();
			}

			if($_GET['action']=='goods')
			{
				$table = new cTable('goods','adv_'.$adv_cat['eng'],'id',$is_rank=0, $is_status=0, $is_add=1,$is_edit=1,$is_del=1);
				$table->settings(' cat_id='.$_GET['cat_id']);
				$table->insertparam('cat_id', $_GET['cat_id']);
				$table->insertcol('Subject','subject',1,1,'text',$size='',$max='');
				$table->insertcol('Body','body',1,1,'textarea',$size='30',$max='5');

				$select = Array();
				DB::query("select * from adv_razd where cat_id=".$_GET['cat_id']." order by name");
				while ($row = DB::fetch_row()) $select[$row['id']] = $row['name'];
				$table->insertcol('Subcategory','razd_id',1,1,'select',$select);
				switch($adv_cat['eng']){
					case 'jobs':
						$select = Array();
						$select[0] = 'no';
						$select[1] = 'yes';
						$table->insertcol('telecommute','telecommute',1,1,'select',$select);
						$select = Array();
						$select[0] = 'no';
						$select[1] = 'yes';
						$table->insertcol('contract','contract',1,1,'select',$select);
						$select = Array();
						$select[0] = 'no';
						$select[1] = 'yes';
						$table->insertcol('internship','internship',1,1,'select',$select);
						$select = Array();
						$select[0] = 'no';
						$select[1] = 'yes';
						$table->insertcol('part-time','part_time',1,1,'select',$select);
						$select = Array();
						$select[0] = 'no';
						$select[1] = 'yes';
						$table->insertcol('non-profit','non_profit',1,1,'select',$select);
						break;
					case 'myspace':
						$table->insertcol('age','age',1,1,'text',$size='',$max='');
						break;
					case 'housting':
						$table->insertcol('rent','rent',1,1,'text',$size='',$max='');
						$select = Array();
						$select[0] = '0';
						$select[1] = '1';
						$select[2] = '2';
						$select[3] = '3';
						$select[4] = '4';
						$select[5] = '5';
						$table->insertcol('br','br',1,1,'select',$select);
						break;
					case 'services':
						$table->insertcol('price','price',1,1,'text',$size='',$max='');
						break;
					case 'casting':
						$table->insertcol('age','age',1,1,'text',$size='',$max='');
						break;
					case 'personals':
						$table->insertcol('age','age',1,1,'text',$size='',$max='');
						break;
					case 'sale':
						$table->insertcol('price','price',1,1,'text',$size='',$max='');
						break;
					case 'cars':
						$table->insertcol('price','price',1,1,'text',$size='',$max='');
						break;
				}
				ob_start();
				$table->draw();
				$content .= ob_get_clean();
			}
		}
		$html->setvar('content', $content);
		parent::parseBlock($html);
	}
}

$page = new CAdninAdv("", $g['tmpl']['dir_tmpl_administration'] . "adv.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
