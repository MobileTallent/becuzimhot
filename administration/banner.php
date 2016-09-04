<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$banners_path = $g['path']['url_files'] . 'banner/';

class CForm extends CHtmlBlock
{
	var $message = '';

	function action()
	{
		global $g;
		$act = get_param('act');
		$del = get_param('delete', 0);

		if ($del != 0) {
			$banner_filename = DB::result("SELECT filename FROM banners WHERE id='".addslashes($del)."' LIMIT 1");
            $file = $g['path']['dir_files'] . "banner/" . $banner_filename;
            Common::saveFileSize($file, false);
			@unlink($file);
			DB::execute("DELETE FROM banners WHERE id=".intval($del)."");
		} elseif ($act == 'banners_off') {
			$id = get_param('id');
			if ($id != '') {
				DB::execute("UPDATE banners SET active='0' WHERE id=".intval($id)."");
			}
			global $p;
			redirect($p."?action=saved");
		} elseif ($act == 'banners_on') {
			$id = get_param('id');
			if($id != '') {
				DB::execute("UPDATE banners SET active='1' WHERE id=".intval($id)."");
			}
			global $p;
			redirect($p."?action=saved");
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $banners_path;

		$html->setvar('message', $this->message);

		$act = get_param('act');

        $place_before = '';
        $whereNotAllow = CBanner::getAllowBannerSql('B',1);
        $sql = "SELECT B.name, B.id, B.active, B.type, B.filename, B.alt, B.place, B.code, B.width, B.height, B.url
                  FROM banners as B,
                       banners_places as BP
                 WHERE B.place = BP.place
                 ".$whereNotAllow."
                 ORDER BY BP.id";
        $banners_query = DB::query($sql);

        $bannersCount = DB::num_rows();
        if ($bannersCount == 0) {
            $html->parse('no_banners');
        }

        while(list($name, $id, $active, $type, $filename, $alt, $place, $code, $width, $height, $url) = DB::fetch_row()) {

            $html->setvar('banner_id', $id);

            $placeSrc = $place;

            if ($place != $place_before) {
                $place = str_replace('_', ' ', $place);
                $html->setvar('place', $place);
                $html->parse('place', false);
            } else {
                $html->setblockvar('place', '');
            }

            $html->setvar('banner_filename', $name);
            if($type == 'flash') {
                $flashHtml =  User::flashBanner($filename, $width, $height);
                $html->setvar('flash_code', $flashHtml);
                $html->parse('flh', false);
                $html->setblockvar('img', '');
                $html->setblockvar('code', '');
                $html->setblockvar('show', '');
            } elseif ($type == 'code') {
                $html->setvar('banner_code', htmlspecialchars($code));
                $html->parse('show', false);
                $html->parse('code', false);
                $html->setblockvar('img', '');
                $html->setblockvar('flh', '');
            } else {
                $html->setvar('banner_path', $banners_path . $filename);
                $html->setvar('banner_alt', $alt);
                if (trim($url)) {
                    $html->setvar('banner_url', $url);
                    $html->parse('banner_url', false);
                }
                $html->parse('img', false);
                $html->setblockvar('show', '');
                $html->setblockvar('code', '');
                $html->setblockvar('flh', '');
            }


            if ($active == 1) {
                $html->setvar('wswitch', l('off'));
                $html->setvar('switch', 'off');
            } else {
                $html->setvar('wswitch', l('on'));
                $html->setvar('switch', 'on');
            }

            $place_before = $placeSrc;

            $html->parse('banner', true);
        }

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "banner.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
