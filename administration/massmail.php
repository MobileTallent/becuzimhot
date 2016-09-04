<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminMailMass extends CHtmlBlock {

    var $message_send = '';

    function action()
    {
        global $g;

        $cmd = get_param('cmd', '');
        $lang = get_param_array('language');

        if ($cmd == 'send') {
            $whereLang = '';
            if (count($lang) > 0) {
                $whereLangParts = array();
                foreach ($lang as $language => $value) {
                    if($lang == 'default') {
                        $whereLangParts[] = "''";
                    }
                    $whereLangParts[] = to_sql($language);
                }
                $whereLangValue = implode(',', $whereLangParts);
                if ($whereLangValue != '') {
                    $whereLang = ' AND lang IN (' . $whereLangValue . ')';
                }
            }

            $subject = get_param('subject', '');
            $text = get_param('text', '');
            $to = trim(get_param('to', ''));

            $toPartners = get_param('partners', '');
            $toUsers = get_param('users', '');
            $toOther = get_param('other', '');

            if ($to != '') {
                if(Common::validateEmail($to)) {
                    send_mail($to, $g['main']['info_mail'], $subject, $text);
                    $this->message_send = l('sent');
                } else {
                    $this->message_send = l('Specific email is incorrect');
                }
            } elseif($whereLang == '') {
                $this->message_send = l('Please choose languages');
            } else {
                if ($toPartners == '1') {
                    DB::query("SELECT mail FROM partner WHERE 1 $whereLang");
                    while ($row = DB::fetch_row()) {
                        send_mail($row['mail'], $g['main']['info_mail'], $subject, $text);
                    }
                }

                if ($toUsers == '1' || $toOther == '1') {
                    $sql = 'SELECT e.mail, u.user_id FROM email AS e '
                        . 'LEFT JOIN user AS u ON e.mail=u.mail ' . $whereLang;
                    //echo $sql;
                    DB::query($sql);
                    while ($row = DB::fetch_row()) {
                        if ($toUsers == '1' && $row['user_id'] != 0) {
                            #echo $row['mail'] . "<br>";
                            send_mail($row['mail'], $g['main']['info_mail'], $subject, $text);
                        }
                        if ($toOther == '1' && $row['user_id'] == 0) {
                            #echo $row['mail'] . "<br>";
                            send_mail($row['mail'], $g['main']['info_mail'], $subject, $text);
                        }
                    }
                }

                $this->message_send = l('sent');
            }


        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;
        $html->setvar("message_send", $this->message_send);
        $html->setvar("to", get_param("to", ""));
        $html->setvar("subject", htmlspecialchars(get_param('subject', '')));
        $html->setvar("text", get_param("text", ""));

        $lang = get_param_array('language');

        $langs = Common::listLangs();

        $langActive =  Common::getOption('main', 'lang_value');
        $langCurrentKey = array_search($langActive, $langs);

        if($langCurrentKey !== false) {
            $langCopy = $langs[$langCurrentKey];
            unset($langs[$langCurrentKey]);
            $langs = array($langCurrentKey => $langCopy) + $langs;
        }

        if ($langs) {
            foreach ($langs as $title => $file) {
                $html->setvar('language_value', $file);
                $html->setvar('language_title', $title);

                $languageChecked = '';

                if (isset($lang[$file]) && $lang[$file] == 1) {
                    $languageChecked = 'checked';
                }

                $html->setvar('language_checked', $languageChecked);
                $html->parse('language');
            }
        }

        $lang = Common::getOption('administration', 'lang_value');
        $langTinymceUrl =  $g['tmpl']['url_tmpl_administration'] . "js/tinymce/langs/{$lang}.js";
        if (!file_exists($langTinymceUrl)) {
            $lang = 'default';
        }
        $html->setvar('lang_vw', $lang);

        parent::parseBlock($html);
    }

}

$page = new CAdminMailMass("", $g['tmpl']['dir_tmpl_administration'] . "massmail.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>
