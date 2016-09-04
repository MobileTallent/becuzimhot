<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminForget extends CHtmlBlock {

    var $message = '';
    var $sent = false;

	function action($redirect = true)
	{
        parent::action();

		$mail = trim(get_param('mail' , ''));

		if ($mail != '') {
            $mailAdmin = Common::getOption('info_mail', 'main');

			if ($mail == $mailAdmin) {
                $pass = Common::getOption('admin_password', 'main');

                $vars = array(
                        'title' => Common::getOption('title', 'main'),
                        'name' => 'admin',
                        'mail' => $mailAdmin,
                        'password' => $pass,
                );
                Common::sendAutomail(Common::getOption('lang_loaded', 'main'), $mailAdmin, 'forget', $vars);

                $this->message = l('sent');
			} else {
				$this->message = l('mail_incorrect');
			}
		}
	}

	function parseBlock(&$html)
	{
		$html->setvar("message", $this->message);

        if($this->message) {
            $html->parse('alert');
        }
		parent::parseBlock($html);
	}
}

$page = new CAdminForget('', $g['tmpl']['dir_tmpl_administration'] . 'forget_password.html');
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");