<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
include("../_include/current/music/tools.php");

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
	        $song_id = get_param('song_id');
	        DB::query("SELECT m.* ".
	            "FROM music_song as m ".
	            "WHERE m.song_id=" . to_sql($song_id, 'Number') . " LIMIT 1");
	        if($song = DB::fetch_row())
	        {
                $title = get_param('song_title');
                $year = get_param('song_year');
                $about = get_param('song_about');
                
                DB::execute('UPDATE music_song SET song_title=' . to_sql($title) . 
                    ', song_year=' . to_sql($year) .
                    ', song_about=' . to_sql($about) .
                    ', updated_at=NOW() WHERE song_id=' . $song['song_id']);
                                
                redirect("music_song_edit.php?action=saved&song_id=".$song['song_id']);
	        }
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $song_id = get_param('song_id');
        DB::query("SELECT m.* ".
            "FROM music_song as m ".
            "WHERE m.song_id=" . to_sql($song_id, 'Number') . " LIMIT 1");
        if($song = DB::fetch_row())
        {
        	$html->setvar('song_id', $song['song_id']);
        	$html->setvar('song_title', he($song['song_title']));
        	$html->setvar('song_about', $song['song_about']);
        	$html->setvar('song_year', $song['song_year']);

	        $song_year_options = '';
	        $current_year = intval(date("Y", time()));
	        for($year = $current_year; $year != $current_year - 101; --$year)
	        {
	            $song_year_options .= '<option value=' . $year . ' ' . (($year == $song['song_year']) ? 'selected="selected"' : '') . '>';
	            $song_year_options .= $year; 
	            $song_year_options .= '</option>';           
	        }
	        $html->setvar("song_year_options", $song_year_options);
        	
            DB::query("SELECT * FROM music_song_image WHERE song_id=" . $song['song_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "music_song_images/" . $image['image_id'] . "_b.jpg");
                $html->setvar("image_id", $image['image_id']);
                $html->parse("image");
                
                $n_images++;
                
                $html->parse('photo');
            }
            
            $html->setvar('song_player', 
                CMusicTools::song_player(
                    $song['song_id'], 
                    $song['song_length'], 
                    1, 
                    "BigClipPlayer.swf", 
                    264, 
                    26));            
            
            $html->parse('photo_edit');
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "music_song_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");