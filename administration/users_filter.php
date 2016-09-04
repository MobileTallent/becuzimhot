<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminUsersFilter extends CHtmlBlock
{

	var $section = 'deny_words';

	function action()
	{
        global $p;
        
		$cmd = get_param('cmd', '');
		$words = get_param_array('w');

		if ($cmd == 'update') {
            Config::remove($this->section);
            foreach ($words as $key => $value) {
                if(trim($value) == '') {
                    unset($words[$key]);
                }
            }
            Config::addAll($this->section, $words);
            redirect($p . '?action=saved');
		}
	}

	function parseBlock(&$html)
	{
        $words = Config::getOptionsAll($this->section, 'option', 'ASC');
        if($words) {
            foreach ($words as $key => $value) {
                $html->setvar('value', htmlspecialchars($value));
                $html->parse('input', true);
            }
        }

		parent::parseBlock($html);
	}
}

$page = new CAdminUsersFilter("", $g['tmpl']['dir_tmpl_administration'] . "users_filter.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>