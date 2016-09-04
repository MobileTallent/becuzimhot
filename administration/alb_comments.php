<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
include("../_include/current/pager.php");
class CPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $page = (intval(param('p')) < 1 ? 1 : intval(param('p')));
        $pagerOnPage = 20;
        $pagerUrl = g('path','url_administration') . 'alb_comments.php?p=%s';

        $itemsTotal = DB::count('gallery_comments');
        $items = array();
        if ($page == 1){
            $items = DB::all("SELECT id,user_id,imageid,name,date,comment FROM gallery_comments LIMIT ". to_sql($pagerOnPage,"Number") . ";");
        }  else {
            $items = DB::all("SELECT id,user_id,imageid,name,date,comment FROM gallery_comments LIMIT ". to_sql(($page - 1)*$pagerOnPage,"Number") . ",". to_sql($page * $pagerOnPage,"Number") . ";");
        }


       if ($itemsTotal > $pagerOnPage) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            $html->assign('pager', $pager->getAbPages());
            $html->assign('itemsTotal', $itemsTotal);
            $html->parse('pages');
        }
        $html->items('item', admin_color_lines($items), null);
        $html->cond(count($items) > 0, 'items', 'noitems');

		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "alb_comments.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>