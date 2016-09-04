<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/vids/includes.php");

class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g_options;
        global $p;

		$video = get_param_array("do");
		$redirect = false;

		foreach ($video as $k => $v){
			if ($v == 'add') {
				DB::execute("UPDATE vids_video SET active=1 WHERE id=" . ((int) $k) . "");
                /*
				DB::query("SELECT * FROM vids_video WHERE id=" . ((int) $k) . "", 2);
				if ($row = DB::fetch_row(2)) {
					DB::execute("UPDATE user SET is_photo='Y' WHERE user_id=" . $row['user_id'] . "");
                    User::setAvailabilityPublicPhoto($row['user_id']);
				}
                 *
                 */
                DB::update('wall', array('params' => ""), '`item_id` = ' . to_sql((int) $k).' AND section="vids"');
				$redirect = true;
			} elseif ($v == 'del') {
				DB::query("SELECT * FROM vids_video WHERE id=" . ((int) $k) . "", 2);
				if ($row = DB::fetch_row(2)) {
					//deletephoto($row['user_id'], $row['photo_id']);
                    CVidsTools::delVideoById($row['id'],true);
				}
				$redirect = true;
			}
		}


		if($redirect) redirect($p."?action=saved");

	}

	function parseBlock(&$html)
	{
		global $g_options;

		$html->setvar("message", $this->message);

		$table = get_param("t", "tips");
		$html->setvar("table", $table);

        $html->setvar('photo_height', Common::getOption('medium_y', 'image'));

		DB::query("SELECT * FROM vids_video WHERE active=3 ORDER BY id LIMIT 20");
		$num=DB::num_rows();
		while ($row = DB::fetch_row())
		{
			$row['user_name'] = DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2);
			foreach ($row as $k => $v)
			{
				$html->setvar($k, $v);
			}

            $video = CVidsTools::getVideoById($row['id'], true);
            if (!isset($video) or !is_array($video)) {
                continue;
            }
            $html->setvar('video_html_code', $video['html_code']);

            $html->setvar('video_id', $row['id']);
            if ($row['private'] == '1'){
                $html->setvar('private', l('Private'));
            } else {
                $html->setvar('private', l('Public'));
            }
			$html->parse("video", true);
		}
		if($num==0){
			$html->parse("msg",true);
		} else {
			$html->parse("videos",true);
		}
		parent::parseBlock($html);
	}
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_video.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");