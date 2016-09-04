<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminMenu extends CHtmlBlock
{

	function action()
	{
		global $p;

		$cmd = get_param('cmd', '');

		if ($cmd == 'update') {
            $menu = get_param_array('menu');
            $options = array_flip($menu);

            Config::updateAll('menu', $options);

            $status = get_param_array('status');
            foreach ($options as $option => $value) {
                Config::updatePosition('menu', $option, isset($status[$option])?1:0);
            }
			redirect($p . '?action=saved');
		}
	}

	function parseBlock(&$html)
	{
        global $l;
        global $p;
        global $g;

        $menu = Menu::mainMenuItems(true);
        // Do not need such a disconnected menu.class.php line:146
        if (Common::isMultisite()) {
            unset($menu['city']);
        }
        $lang = loadLanguageAdmin();
		foreach ($menu as $key => $value) {

            $langKey = Menu::itemLangKey($key);

            $menuTitle = l($langKey, $lang);

            $html->setvar('menu_key', $key);
			$html->setvar('menu_value', $value);
			$html->setvar('menu_title', $menuTitle);
            $html->setvar('checked', Config::getPosition('menu', $key)?'checked':'');
			$html->parse('button', true);
		}

		parent::parseBlock($html);
	}
}

$page = new CAdminMenu("", $g['tmpl']['dir_tmpl_administration'] . "menu.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuOptions());

include("../_include/core/administration_close.php");
?>