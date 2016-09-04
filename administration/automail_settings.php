<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminMailAutoSettings extends  CAdminOptions{

    var $message_auto = "";

    function init() {
        $this->setBlock(array('logo_mail' => 1));
        parent::init();
    }

    function action()
    {
        global $g;

        parent::action();

        $cmd = get_param('cmd', '');

        if ($cmd == 'edit') {
            $lang    = get_param('lang', 'default');
            $options = get_param_array('option');

            if (is_array($options) && count($options)) {
                foreach ($options as $option => $value) {
                    $sql = 'INSERT INTO `email_auto_settings`
                               SET `value` = ' . to_sql($value, 'Text') . ',
                                  `option` = ' . to_sql($option, 'Text')
                            . ' ON DUPLICATE KEY UPDATE
                                   `value` = ' . to_sql($value, 'Text') . ',
                                  `option` = ' . to_sql($option, 'Text');
                    DB::execute($sql);
                }
            }

            global $p;
            redirect($p . '?lang=' . $lang . '&action=saved');
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;
        $html->setvar("message_auto", $this->message_auto);

        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        $sql = 'SELECT * FROM `email_auto_settings` '
              . 'WHERE `option` = ' . to_sql($languageCurrent, 'Text')
                . ' OR `option` = "template"';
        $rows = DB::rows($sql);
        foreach ($rows as $row) {
             if (in_array($row['option'], Common::listLangs('main'))) {
                 $html->setvar('signature', he($row['value']));
             } else {
                 $html->setvar($row['option'], $row['value']);
             }
         }

        adminParseLangsModule($html, $languageCurrent);

        parent::parseBlock($html);
    }

}

$page = new CAdminMailAutoSettings("", $g['tmpl']['dir_tmpl_administration'] . "automail_settings.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>
