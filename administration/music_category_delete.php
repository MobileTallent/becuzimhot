<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/music/tools.php");
function do_action()
{
	global $g_user;
	
	$id = intval(get_param('category_id'));
	if($id)
	{
        DB::query("SELECT musician_id FROM music_musician WHERE category_id=$id",4);
		while($row=DB::fetch_row(4))
		{
            CMusicTools::delete_musician($row['musician_id'], true);

		}
		DB::execute("DELETE FROM music_category WHERE category_id=$id");
		
	}
	
	$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "administration/music_categories.php"; 
    redirect($return_to);
}

do_action();

include("../_include/core/administration_close.php");
