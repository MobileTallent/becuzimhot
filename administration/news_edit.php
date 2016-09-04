<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CEditNews extends CHtmlBlock
{
	var $message = "";

	function action()
	{
		$cmd = get_param("cmd", "");
		$action = get_param("action", "");

		if ($cmd == "edit" && $action!="saved" )
		{
			$id = get_param("id", "");
			$title = get_param("title", "");
			$cat = get_param("cat", "");
			$date = get_param('news_date',"");
			$news_short = get_param("news_short", "");
			$news_long = get_param("news_long", "");
			$root = get_param("root", "");
			$root_id = get_param("root_id", "");
			
			DB::query("SELECT * FROM news WHERE id=" . to_sql($id, "Number") . "");
			$row = DB::fetch_row();
			
			$date = explode('/',$date);
			
			if(empty($date[0]) || empty($date[1]) || empty($date[2])) {
			    $date[0] = date('m',$row['dt']);
			    $date[1] = date('d',$row['dt']);
			    $date[2] = date('Y',$row['dt']);
			}
			
			$time = mktime(date('H',$row['dt']), date('i',$row['dt']), date('s',$row['dt']), $date[0], $date[1], $date[2]);
            
	    if($root) {
                $root_id = 0;
            }

			$this->message = "";
			
			if ($this->message == "")
			{
			    
				DB::execute("
					UPDATE news
					SET
					title=" . to_sql($title, "Text") . ",
					cat=" . to_sql($cat, "Number") . ",
					dt =" . to_sql($time,"Number") . ",
					news_short=" . to_sql($news_short, "Text") . ",
					news_long=" . to_sql($news_long, "Text") . ",
					root=" . to_sql($root, "Text") . ",
					root_id=" . to_sql($root_id, "Text") . "
					WHERE id=" . to_sql($id, "Number") . "
				");
				global $p;
				redirect($p."?action=saved&id=$id&cmd=edit");
			}
		}
	}

	function parseBlock(&$html)
	{
		global $g;

        $lang = Common::langParamValue();
        $html->setvar('lang', $lang);

		$id = get_param("id", "");
		DB::query("SELECT * FROM news WHERE id=" . to_sql($id, "Number") . "");
		$row = DB::fetch_row();
		
		$date = date('m/d/Y',$row['dt']);
		
		$html->setvar("date", get_param("dt", $date));
		$html->setvar("id", get_param("id", $row['id']));
		$html->setvar("title", get_param("title", htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8')));
		$html->setvar("cat", get_param("cat", $row['cat']));
		$html->setvar("news_short", get_param("news_short", $row['news_short']));
		$html->setvar("news_long", get_param("news_long", $row['news_long']));
        $root = '';
        if(get_param('root', $row['root']) == 1) {
            $root = 'checked';
        }
		$html->setvar('root', $root);

        if($row['lang'] == 'default') {
            $html->parse('root_page');
        }

        $sql = 'SELECT COUNT(*) FROM news
            WHERE root = 1 AND id != ' . to_sql($id);
        $rootPagesCount = DB::result($sql);

        if($rootPagesCount && $row['lang'] != 'default') {
            $rootPagesOptions = "<option value='0'></option>\r\n";
            $rootPagesOptions .= DB::db_options('SELECT id, title FROM news WHERE root = 1 AND id != ' . to_sql($id), get_param('root_id', $row['root_id']));

            $html->setvar('root_pages_options', $rootPagesOptions);

            if(!$root) {
                $html->parse('root_pages');
            }
        }

		$cat_options = "<option value=\"0\">" . l('No category') . "</option>\r\n";
		$cat_options .= DB::db_options("SELECT id, title FROM news_cats WHERE lang = " . to_sql($lang), get_param("cat", $row['cat']));
		$html->setvar("cat_options", $cat_options);

		parent::parseBlock($html);
	}
}

$page = new CEditNews("", $g['tmpl']['dir_tmpl_administration'] . "news_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
