<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

if (Common::isMultisite()) {
    unset($g['games']['tanks']);
}
            
class CAdminMenu extends CHtmlBlock
{

	function action()
	{
		global $p;
        global $g;
        
		$cmd = get_param('cmd', '');
		if ($cmd == 'update') {
            $position = get_param_array('games');
            $status = get_param_array('games_status');
            $position = array_flip($position);
            foreach ($g['games'] as $key => $value) {
               Config::updatePosition('games', $key, $position[$key]);
               $resultStatus[$key] = (isset($status["$key"])) ? 1 : 0;
            }
            Config::updateAll('games', $resultStatus);
			redirect($p . '?action=saved');
		}
	}

	function parseBlock(&$html)
	{
        global $g;

		foreach ($g['games'] as $key => $value) {
            $html->setvar('game_key', $key);
            if ($value) 
                $html->setvar('game_checked', 'checked');
            else 
                $html->setvar('game_checked', '');
			$html->setvar('game_title', l($key));
			$html->parse('game_item', true);
		}

		parent::parseBlock($html);
	}
}

$page = new CAdminMenu('', $g['tmpl']['dir_tmpl_administration'] . 'games.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

include('../_include/core/administration_close.php');

?>