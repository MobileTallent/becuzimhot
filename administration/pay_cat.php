<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("../_include/core/administration_start.php");

class CHon extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_user;
		$action = get_param('action');

        $cat_id = intval(get_param('cat_id'));

		switch($action){
			case 'add_cat':
				$cat_name = get_param('cat_name');
				$code = get_param('code');
				if(!empty($cat_name))
					DB::execute("insert into payment_cat set name=".to_sql($cat_name).", code=".to_sql($code));

				break;
			case 'change_cat':
				$cat_name = get_param('cat_name');
				$code = get_param('code');

				if(!empty($cat_name) && !empty($cat_id)) {
                    $codeOld = $this->getCode($cat_id);
					DB::execute("update payment_cat set name=".to_sql($cat_name)." , code=".to_sql($code)." where id=".$cat_id);
                    DB::update('payment_type', array('code' => $code), 'code = ' . to_sql($codeOld));
                }
				break;
			case 'del_cat':
				if(!empty($cat_id)){
                    $code = $this->getCode($cat_id);
                    DB::delete('payment_type', 'code = ' . to_sql($code));
					DB::delete('payment_cat', 'id = ' . $cat_id);
				}
				break;


		}
		if($action=="change_cat")
		{
			global $p;
			redirect($p."?action=saved");
		}

	}

    function getCode($id)
    {
        $sql = 'SELECT code FROM payment_cat WHERE id = ' . to_sql($id);
        return DB::result($sql);
    }

	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

//			$html->setvar("select_cats", DB::db_options("select id, name from adv_cats order by name"));
		$action = get_param('action');
		switch($action){
			case 'edit_cat':
				$cat_id = get_param('cat_id');
				if(!empty($cat_id)){
					DB::query("select * from payment_cat where id=".$cat_id);
					$row = DB::fetch_row();
					$html->setvar("cat_id", $row['id']);
					$html->setvar("cat_name", he($row['name']));
					$html->setvar("code", he($row['code']));
					$html->parse("cat_edit", true);
				}
				break;
			default:
				if ($html->blockexists("group")){
					DB::query("select * from payment_cat order by name");
                    $i = 0;
					while($row = DB::fetch_row()){
                        $i++;
                        if ($i % 2 == 0) {
						    $html->setvar("class", 'color');
						    $html->setvar("decl", '_l');
						    $html->setvar("decr", '_r');
                        } else {
						    $html->setvar("class", '');
						    $html->setvar("decl", '');
						    $html->setvar("decr", '');
                        }
						$html->setvar("var_id", $row['id']);
						$html->setvar("var_name", $row['name']);
						$html->setvar("var_code", $row['code']);
						$html->parse("group_item", true);
					}
					$html->parse("group", true);
				}
		}




		parent::parseBlock($html);
	}
}

$page = new CHon("", $g['tmpl']['dir_tmpl_administration'] . "pay_cat.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);

$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include("../_include/core/administration_close.php");

?>
