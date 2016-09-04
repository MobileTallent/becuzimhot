<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{
	var $message = "";
	var $login = "";
	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "update")
		{
	        $id = get_param("id");
	        DB::query("SELECT r.* ".
	            "FROM places_review as r ".
	            "WHERE r.id=" . to_sql($id, 'Number') . " LIMIT 1");
	        if($review = DB::fetch_row())
	        {
	            $title = get_param('review_title');
                $text = get_param('review_text');
                            
                if($title && $text)
                {
                    DB::execute('UPDATE places_review SET title=' . to_sql($title) . ', text=' . to_sql($text) . 
                        ', updated_at=NOW() WHERE id=' . $review['id']);
                                
									global $p;
									redirect("$p?action=saved&id=".get_param("id"));
                }
	        }
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $id = get_param('id');
        DB::query("SELECT r.* ".
            "FROM places_review as r ".
            "WHERE r.id=" . to_sql($id, 'Number') . " LIMIT 1");
        if($review = DB::fetch_row())
        {
        	$html->setvar('user_id', $review['user_id']);
        	$html->setvar('review_id', $review['id']);
        	$html->setvar('review_title', he($review['title']));
        	$html->setvar('review_text', $review['text']);
        	
            DB::query("SELECT * FROM places_place_image WHERE place_id=" . $review['place_id'] . " AND user_id = " . $review['user_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "places_images/" . $image['id'] . "_b.jpg");
                $html->setvar("image_id", $image['id']);
                $html->parse("image");
                
                $n_images++;
                
                $html->parse('photo');
            }
            
            if($n_images>0) $html->parse('photos');
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "places_review_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");