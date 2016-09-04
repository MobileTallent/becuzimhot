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
        $pagerUrl = g('path','url_administration') . 'alb_albums.php?p=%s';

        $itemsTotal = DB::query('SELECT COUNT(DISTINCT `user_id`) FROM `gallery_albums`');
        $items = array();
        if ($page == 1){
            $ids = DB::all("SELECT DISTINCT `user_id` FROM gallery_albums LIMIT ". to_sql($pagerOnPage,"Number") . ";");
        }  else {
            $ids = DB::all("SELECT DISTINCT `user_id` FROM gallery_albums LIMIT ". to_sql(($page - 1)*$pagerOnPage,"Number") . ",". to_sql($page * $pagerOnPage,"Number") . ";");
        }
        for ($index = 0; $index < count($ids); $index++) {
            $data[$index]['username'] = DB::row("SELECT `name` FROM `user` WHERE user_id = " . to_sql($ids[$index]['user_id'], "Number") . " LIMIT 1");
            $data[$index]['username'] = $data[$index]['username']['name'];
            $data[$index]['user_id'] = $ids[$index]['user_id'];
            $data[$index]['count_albums'] = DB::count('gallery_albums','`user_id` = '.  to_sql($ids[$index]['user_id'],'Number').'');
            $data[$index]['count_images'] = DB::count('gallery_images','`user_id` = '.  to_sql($ids[$index]['user_id'],'Number').'');
            $data[$index]['count_comments'] = DB::count('gallery_comments','`user_id` = '.  to_sql($ids[$index]['user_id'],'Number').'');
            $data[$index]['count_views'] = DB::row("SELECT SUM(`views`) FROM `gallery_albums` WHERE `user_id` = " . to_sql($ids[$index]['user_id'], "Number") . " LIMIT 1");
            $data[$index]['count_views'] = $data[$index]['count_views'][0];
            #print_r($items[$index]);
        }

       if ($itemsTotal > $pagerOnPage) {
            $pager = new Pager($page, $itemsTotal, $pagerUrl, $pagerOnPage);
            $html->assign('pager', $pager->getAbPages());
            $html->assign('itemsTotal', $itemsTotal);
            $html->parse('pages');
        }
        $html->items('item', admin_color_lines($data), null);
        $html->cond(count($data) > 0, 'items', 'noitems');

		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "alb_users.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>