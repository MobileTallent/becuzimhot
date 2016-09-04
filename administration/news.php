<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CNews extends CHtmlList
{
	var $m_on_page = 10;
	function action()
	{
		global $g;

        $lang = Common::langParamValue();

		$del = get_param("delete", "");

		if ($del != "")
		{
			DB::execute("DELETE FROM news WHERE id=" . to_sql($del, "Number") . "");
			for ($i = 1; $i <= 5; $i++)
			{
				if (file_exists($g['path']['dir_files'] . "news/" . $del . "_" . $i . "_s.jpg")) unlink($g['path']['dir_files'] . "news/" . $del . "_" . $i . "_s.jpg");
				if (file_exists($g['path']['dir_files'] . "news/" . $del . "_" . $i . "_s.jpg")) unlink($g['path']['dir_files'] . "news/" . $del . "_" . $i . "_m.jpg");
				if (file_exists($g['path']['dir_files'] . "news/" . $del . "_" . $i . "_s.jpg")) unlink($g['path']['dir_files'] . "news/" . $del . "_" . $i . "_b.jpg");
			}

			global $p;
			redirect($p);

		}

		$vis = get_param("visible", "");

		if ($vis != "")
		{
			$v = DB::result("SELECT visible FROM news WHERE id=" . to_sql($vis, "Number") . "");
			$v = (($v == "Y") ? "N" : "Y");
			DB::execute("UPDATE news SET visible='". $v . "' WHERE id=" . to_sql($vis, "Number") . "");
			global $p;
			redirect($p."?action=saved");
		}
	}
	function init()
	{
		parent::init();

		$this->m_sql_count = "SELECT COUNT(u.id) FROM news AS u" . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT n.id, n.visible, n.title, n.news_short, n.news_long, n.dt, n.cat
			FROM news AS n
			" . $this->m_sql_from_add . "
		";

		$this->m_field['id'] = array("id", null);
		$this->m_field['title'] = array("title", null);
		$this->m_field['news_short'] = array("news_short", null);
		$this->m_field['news_long'] = array("news_long", null);
		$this->m_field['dt'] = array("dt", null);
		$this->m_field['day'] = array("day", null);
		$this->m_field['visible'] = array("visible", null);
		$this->m_field['cat'] = array("cat", null);


        $lang = Common::langParamValue();
		$this->m_sql_where = "1 AND lang = " . to_sql($lang);
		$this->m_sql_order = "n.dt DESC";
	}
	function parseBlock(&$html)
	{
        $lang = Common::langParamValue();
        $html->setvar('lang', $lang);
		parent::parseBlock($html);
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		$this->m_field['dt'][1] = date("/m/y", $row['dt']);
		$this->m_field['day'][1] = date("d", $row['dt']);
		$this->m_field['news_short'][1] = nl2br($row['news_short']);
		$this->m_field['cat'][1] = DB::result("SELECT title FROM news_cats WHERE id=" . $row['cat'] . "", 0, 2);
		if ($this->m_field['cat'][1] == "")
		{
			$this->m_field['cat'][1] = l('No category');
		}

		$this->m_field['visible'][1] = $row['visible'] == "Y" ? l('Visible') : l('Hidden');
	}
}

$page = new CNews("news", $g['tmpl']['dir_tmpl_administration'] . "news.html");

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
