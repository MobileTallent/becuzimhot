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
	        $musician_id = get_param('musician_id');
	        DB::query("SELECT m.* ".
	            "FROM music_musician as m ".
	            "WHERE m.musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
	        if($musician = DB::fetch_row())
	        {
	            $category_id = get_param('category');
                $country_id = get_param('country');
                $name = get_param('musician_name');
                $leader = get_param('musician_leader');
                $founded = get_param('musician_founded');
                $about = get_param('musician_about');
                
                DB::execute('UPDATE music_musician SET musician_name=' . to_sql($name) . 
                    ', category_id=' . to_sql($category_id) .
                    ', country_id=' . to_sql($country_id) .
                    ', musician_leader=' . to_sql($leader) .
                    ', musician_founded=' . to_sql($founded) .
                    ', musician_about=' . to_sql($about) .
                    ', updated_at=NOW() WHERE musician_id=' . $musician['musician_id']);
                                
                redirect("music_musician_edit.php?action=saved&musician_id=".$musician['musician_id']); 
	        }
		
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $musician_id = get_param('musician_id');
        DB::query("SELECT m.* ".
            "FROM music_musician as m ".
            "WHERE m.musician_id=" . to_sql($musician_id, 'Number') . " LIMIT 1");
        if($musician = DB::fetch_row())
        {
        	$html->setvar('user_id', $musician['user_id']);
        	$html->setvar('musician_id', $musician['musician_id']);
        	$html->setvar('musician_name', he($musician['musician_name']));
        	$html->setvar('musician_leader', he($musician['musician_leader']));
        	$html->setvar('musician_about', $musician['musician_about']);
        	$html->setvar('musician_founded', $musician['musician_founded']);

            $category_options = '';
            DB::query("SELECT * FROM music_category ORDER BY category_id");
            $lang = loadLanguageAdmin();
            while($category = DB::fetch_row())
            {
                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $musician['category_id']) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], $lang, 'music_category'); 
                $category_options .= '</option>';           
            }
            $html->setvar("category_options", $category_options);

	        $musician_founded_options = '';
	        $current_year = intval(date("Y", time()));
	        for($year = $current_year; $year != $current_year - 101; --$year)
	        {
	            $musician_founded_options .= '<option value=' . $year . ' ' . (($year == $musician['musician_founded']) ? 'selected="selected"' : '') . '>';
	            $musician_founded_options .= $year; 
	            $musician_founded_options .= '</option>';           
	        }
	        $html->setvar("musician_founded_options", $musician_founded_options);
            
            $html->setvar("country_options", DB::db_options("SELECT country_id, country_title FROM geo_country;", $musician['country_id']));
        	
            DB::query("SELECT * FROM music_musician_image WHERE musician_id=" . $musician['musician_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "music_musician_images/" . $image['image_id'] . "_b.jpg");
                $html->setvar("image_id", $image['image_id']);
                $html->parse("image");
                
                $n_images++;
                
                $html->parse('photo');
            }
            
            $html->parse('photo_edit');
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "music_musician_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");