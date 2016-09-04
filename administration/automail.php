<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminMailAuto extends CHtmlBlock {

    var $message_auto = "";

    protected $availableAutoMail = array('urban' => array('mail_message',
                                                          'invite_group_site',
                                                          'invite_group',
                                                          'invite',
                                                          //'wall_alert_message',
                                                          //'wall_alert_like',
                                                          'wall_alert_comment',
                                        ),
                                         'old' => array('new_message',
                                                        'voted_photo',
                                                        'new_comment_photo',
                                                        'new_comment_video',
                                                        'mutual_attraction',
                                                        'want_to_meet_you',
                                                        'gift',
                                                        'profile_visitors',
                                         ),
    );

    function action()
    {
        global $g;
        $cmd = get_param('cmd', '');

        if ($cmd == 'edit') {
            $lang    = get_param('lang', 'default');
            $subject = get_param('subject', '');
            $text    = get_param('text', '');
            $note    = get_param('note', 'join');
            $head    = get_param('header_m', '');
            $button  = get_param('button', '');
            $enabled  = get_param('enabled') == 'on' ? 'Y' : 'N';
            if (isset($g['automail'][$note])) {
                Config::update('automail', $note, $enabled);
            } else {
                Config::add('automail', $note, $enabled, 'max', 0);
            }

            // check that item exists and add otherwise
            $sql = 'INSERT INTO `email_auto`
                       SET `subject` = ' . to_sql($subject, 'Text') . ',
                              `text` = ' . to_sql($text, 'Text') . ',
                              `note` = ' . to_sql($note, 'Text') . ',
                            `header` = ' . to_sql($head, 'Text') . ',
                            `button` = ' . to_sql($button, 'Text') . ',
                              `lang` = ' . to_sql($lang, 'Text')
                    . ' ON DUPLICATE KEY UPDATE
                           `subject` = ' . to_sql($subject, 'Text') . ',
                            `header` = ' . to_sql($head, 'Text') . ',
                            `button` = ' . to_sql($button, 'Text') . ',
                              `text` = ' . to_sql($text, 'Text');
            DB::execute($sql);

            global $p;
            redirect($p . '?note=' . $note . '&lang=' . $lang . '&action=saved');
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;

        $html->setvar("message_auto", $this->message_auto);

        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        $note = get_param('note', 'join');

        $html->setvar('note_current', $note);

        if (!in_array($note, array('invite', 'forget', 'partner_forget'))) {
            $html->setvar("checked", Common::isEnabledAutoMail($note) ? 'checked' : '');
            $html->parse("mail_msg_enabled");
        }

        $sql = 'SELECT * FROM `email_auto` '
              . 'WHERE `lang` = ' . to_sql($languageCurrent, 'Text')
               . ' AND `note` = ' . to_sql($note, 'Text');
        DB::query($sql);

        $lang = Common::getOption('administration', 'lang_value');
        $langTinymceUrl =  $g['tmpl']['url_tmpl_administration'] . "js/tinymce/langs/{$lang}.js";
        if (!file_exists($langTinymceUrl)) {
            $lang = 'default';
        }
        $html->setvar('lang_vw', $lang);

        if ($row = DB::fetch_row()) {
            $html->setvar("note", $row['note']);
            $html->setvar("subject", $row['subject']);
            if (strip_tags($row['text']) == $row['text']) {
               $row['text'] = nl2br($row['text']);
            }
            $html->setvar("text", $row['text']);
            $html->setvar("header_m", $row['header']);
            $html->setvar("button", $row['button']);
            $html->parse("mail_msg", true);
        } else {
            $html->parse("mail_nomsg", true);
        }

        $notMail = "'" . implode("', '", $this->availableAutoMail[Common::getOptionSetTmpl()]) . "'";
        $sql = "SELECT * FROM email_auto"
             . " WHERE lang = 'default' "
               . ' AND `note` NOT IN(' . $notMail . ') '
             . ' ORDER BY id';
        DB::query($sql);
        while ($row = DB::fetch_row()) {
            if ($note == $row['note']) {
                $html->setvar("id", $row['id']);
                $html->setvar("note", $row['note']);
                $html->setvar("note_title", ucfirst(str_replace("_", " ", $row['note'])));
                $html->parse("mail_on", false);
                $html->setblockvar("mail_off", "");
                $html->parse("mail", true);
            } else {
                $html->setvar("id", $row['id']);
                $html->setvar("note", $row['note']);
                $html->setvar("note_title", ucfirst(str_replace("_", " ", $row['note'])));
                $html->parse("mail_off", false);
                $html->setblockvar("mail_on", "");
                $html->parse("mail", true);
            }
        }

        adminParseLangsModule($html, $languageCurrent);

        parent::parseBlock($html);
    }

}

$page = new CAdminMailAuto("", $g['tmpl']['dir_tmpl_administration'] . "automail.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>
