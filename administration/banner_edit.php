<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$banners_path = $g['path']['url_files'] . "banner/";
class CForm extends CHtmlBlock
{
	var $message = "";
	function action()
	{
		global $g;
		$cmd = get_param('cmd', '');
		if($cmd == 'edit')
		{
			$id = get_param('id', 0);
			if($id != 0) {
				$place = str_replace(' ', '_', get_param('place'));
				$active = get_param('active', 0);

                $banner = DB::row('SELECT `type` FROM banners WHERE id = ' . to_sql($id));
                $type = $banner['type'];

                if ($type == 'code') {
                    $sqlPart =  'code = ' . to_sql(get_param('code')) . ',';
                } elseif ($type == 'flash') {
                    $sqlPart =  'width = ' . to_sql(get_param('width'), 'Number') . ','
                              . 'height = ' . to_sql(get_param('height'), 'Number') . ',';
                } else {
                    $sqlPart =  'url = ' . to_sql(get_param('url')) . ','
                              . 'alt = ' . to_sql(get_param('alt')) . ',';
                }

                $sql = 'UPDATE banners '
                        . 'SET active = ' . to_sql($active) . ','
                        . $sqlPart
                        . 'place = ' . to_sql($place) . ','
                        . 'name = ' . to_sql(get_param('name')) . ','
                        . 'templates = ' . to_sql(get_param('template')) . ','
                        . 'langs = ' . to_sql(get_param('lang'))
                        . 'WHERE id = ' . intval($id);
				DB::execute($sql);

				redirect("banner.php?action=saved");
			}
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $banners_path;
		$html->setvar('message', $this->message);
		$id = intval(get_param('id', 0));
		$html->setvar('id', $id);

		$banner = DB::row('SELECT * FROM banners WHERE id = ' . to_sql($id));

        if (!$banner) {
            redirect('banner.php');
        }

        $html->setvar('name', he($banner['name']));
        if ($banner['type'] == 'code') {
            $html->setvar('code', $banner['code']);
            $html->parse('field_code');
        } elseif ($banner['type'] == 'flash') {
            $html->setvar('width', $banner['width']);
            $html->setvar('height', $banner['height']);
            $html->parse('fields_flash');
            $html->parse('field_file');
        } else {
            $html->setvar('url', he($banner['url']));
            $html->setvar('alt', he($banner['alt']));
            $html->parse('fields');
            $html->parse('field_file');
        }

        $whereNotAllow = CBanner::getAllowBannerSql();

        $places = strtolower(DB::db_options('SELECT place as pl, place AS title FROM banners_places' . $whereNotAllow . ' ORDER BY id ASC', ''));

		$places=str_replace("value=\"".$banner['place']."\"","value=\"".$banner['place']."\" selected = \"selected\"",$places);
		$html->setvar("place_opts",str_replace("_", " ",$places));

        $tmpls = array();
        if (countFrameworks('main')) {
           $tmpls = Common::listTmpls('main');
        }
        if (countFrameworks('mobile')) {
           $tmpls = array_merge($tmpls, Common::listTmpls('mobile'));
        }
        $templates = array(l('All') => '') + $tmpls;
        $templates = array_flip($templates);
        $tmplOptions = h_options($templates, $banner['templates']);
        $html->setvar("template_opts", $tmplOptions);

        $langs = Common::listLangs();
        $langs = array(l('All') => '') + $langs;
        $langs = array_flip($langs);
        $langOptions = h_options($langs, $banner['langs']);
		$html->setvar('language_opts', $langOptions);

		if($banner['active'] ==1 ) {
            $html->parse("checked",true);
        }

		parent::parseBlock($html);
	}

}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "banner_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>

