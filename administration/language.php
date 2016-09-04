<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminLang extends CHtmlBlock {

    var $message_lang = "";

    function action()
    {
        global $g;
        global $p;      
        $part = get_param('part', 'main');
        $cmd = get_param('cmd', '');
        $langDir = Common::langPath($part, $g['path']['dir_lang']);
        $langPart = $part;

        if ($cmd == 'add_lang') {
            $langActive = Common::getOption($part, 'lang_value');
            $title = trim(get_param('add_title', ''));
            $title = Common::sanitizeFilename(mb_strtolower($title, 'UTF-8'));
            $title = preg_replace('/[^\p{L}]/u', '', $title);

            $langPath = $langDir . $langPart . '/' . $title . '/';
            if ($title == '' or is_dir($langPath)) {
                $this->message_lang .= "This title already exists or you entered empty title.<br />";
            } else {
                $langSrc = $langDir . $langPart . '/' . $langActive;
                Common::dirCopy($langSrc, $langPath);
                redirect("$p?part=$part");
            }
        } elseif ($cmd == "hide") {
            $hide = get_param('hide');

            if ($hide) {
                if (Common::isLanguageFileExists($hide, $part)) {
                    if (!self::showLanguage($hide, $part) && $hide != Common::getOption($part, 'lang_value')) {
                        Config::add('hide_language_' . $part, $hide, 1);
                    }
                }
            }
            redirect("$p?part=$part");
        } elseif ($cmd == 'verify') {
            $language = Common::getOption($part, 'lang_value');
            include($langDir . $langPart . '/' . strtolower($language) . '/language.php');
            $ld = $l;
            unset($l);
            $dir = $langDir . $langPart . '/';
            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if (is_dir($dir . $file) and substr($file, 0, 1) != '.' and $file != strtolower($language)) {
                            $filename = $dir . $file . '/language.php';
                            include($filename);
                            $to = "<?php\r\n";
                            foreach ($ld as $k => $v) {
                                foreach ($v as $k2 => $v2) {
                                    if (!isset($l[$k][$k2])) {
                                        $l[$k][$k2] = $ld[$k][$k2];
                                    }
                                }
                            }
                            ksort($l);
                            foreach ($l as $k => $v) {
                                foreach ($v as $k2 => $v2) {
                                    $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($l[$k][$k2]) . "\";\r\n";
                                }
                                $to .= "\r\n";
                            }
                            $to .= "?>";

                            unset($l);

                            if (is_writable($filename)) {
                                if (!$handle = @fopen($filename, 'w'))
                                    $this->message_lang .= "Can't open file (" . $filename . ").<br />";
                                if (fwrite($handle, $to) === FALSE)
                                    $this->message_lang .= "Can't write to file(" . $filename . ".).<br />";
                                @fclose($handle);
                            } else
                                $this->message_lang .= "Can't open file (" . $filename . ").<br />";
                        }
                    }
                    closedir($dh);
                    if ($this->message_lang == '') {
                        redirect("$p?action=saved&part=$part");
                    }
                }
            }
        }

        $set = Common::sanitizeFilename(trim(get_param('set', '')));
        $langPath = $langDir . $langPart . '/' . $set . '/';

        if ($set != '' and is_dir($langPath)) {
            self::showLanguage($set, $part);
            Config::update('lang', $part, $set);
            redirect("$p?part=$part");
        }


        $del = Common::sanitizeFilename(trim(get_param('del', '')));
        $langPath = $langDir . $langPart . '/' . $del . '/';

        if ($del != '' and is_dir($langPath)) {
            self::showLanguage($del, $part);
            Common::dirRemove($langPath);
            if (is_dir($langPath)) {
                $this->message_lang .= "Access denied. Please delete manually($dir).<br />";
            } else {
                redirect("$p?part=$part");
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $l;
        global $p;
        $html->setvar("message_lang", $this->message_lang);

        $part = get_param("part", "main");

        $langDir = Common::langPath($part, $g['path']['dir_lang']);
        $langPart = $part;

        $dir = $langDir;
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($dir . $file) and substr($file, 0, 1) != '.') {

                        $langTitle = ucfirst($file);
                        if ($file == 'default') {
                            $langTitle = 'English';
                        }
                        if ($part == $file) {
                            $html->setvar("part", $file);
                            $html->setvar("title", $langTitle);
                            $html->parse("part_on", false);
                            $html->setblockvar("part_off", "");
                        } else {
                            $html->setvar("part", $file);
                            $html->setvar("title", $langTitle);
                            $html->parse("part_off", false);
                            $html->setblockvar("part_on", "");
                        }
                        $html->parse("part", true);
                    }
                }
                closedir($dh);
            }
        }

        $html->setvar('part', $part);
        $dir = $langDir . $langPart . '/';
        $langs = array();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($dir . $file) and substr($file, 0, 1) != '.') {
                        if (Common::isNotAllowedLanguage($file)) {
                            continue;
                        }
                        $langTitle = ucfirst($file);
                        if ($file == 'default') {
                            $langTitle = 'English';
                        }
                        $langs[$file] = $langTitle;
                    }
                }
                closedir($dh);
            }
        }

        natsort($langs);
        $langs = array_flip($langs);
        $langs = Common::sortingLangsList($langs, false, $part);
    
        $langActive = Common::getOption($part, 'lang_value');
    /*
        $langCurrentKey = array_search($langActive, $langs);
    
        if ($langCurrentKey !== false) {
            $langCopy = $langs[$langCurrentKey];
            unset($langs[$langCurrentKey]);
            $langs = array($langCurrentKey => $langCopy) + $langs;
        }
    */    

        foreach ($langs as $k => $v) {
            $html->setvar("language", $v);
            $html->setvar("title", $k);
            if ($g['lang'][$part] == $dir . $v . "/") {
                $html->parse("mlang_on", false);
            } else {
                $html->setblockvar("mlang_on", "");
            }
            $html->parse("mlang", true);
        }

        $i = 0;

        foreach ($langs as $k => $v) {
            $i++;
            if ($i % 2 == 0) {
                $html->setvar("class", 'color');
                $html->setvar("decl", '_l');
                $html->setvar("decr", '_r');
            } else {
                $html->setvar("class", '');
                $html->setvar("decl", '');
                $html->setvar("decr", '');
            }
            $html->setvar("language", $v);
            $html->setvar("title", $k);

            $hide = Common::getOption($v, 'hide_language_' . $part);
            if (!empty($hide)) {
                $html->parse("lang_hide_on", false);
                $html->setblockvar("lang_hide_off", "");
            } else {
                $html->parse("lang_hide_off", false);
                $html->setblockvar("lang_hide_on", "");
            }

            if (/*$v == 'default' && */$langActive == $v) {
                $html->setblockvar("lang_hide_off", "");
                $html->setblockvar("lang_hide_on", "");
                $html->parse("lang_def_on", false);
                $html->setblockvar("lang_def_off", "");
                $html->setblockvar("lang_default_on", "");
            } elseif ($i == 1 && false) {
                $html->setblockvar("lang_hide_off", "");
                $html->setblockvar("lang_hide_on", "");
                $html->setblockvar("lang_default_on", "");
                $html->setblockvar("lang_def_off", "");
                $html->parse("lang_def_on", false);
            } elseif ($v == 'default'  ) {
                $html->setblockvar("lang_def_off", "");
                $html->setblockvar("lang_def_on", "");
                $html->parse("lang_default_on", false);
            } else {
                $html->setblockvar("lang_default_on", "");
                $html->parse("lang_def_off", false);
                $html->setblockvar("lang_def_on", "");
            }

            if ($langActive == $v) {
                $html->parse("lang_on", false);
                $html->setblockvar("lang_off", "");
            } else {
                $html->parse("lang_off", false);
                $html->setblockvar("lang_on", "");
            }
            $html->parse("lang", true);
        }

        parent::parseBlock($html);
    }

    function showLanguage($lang, $part = 'main')
    {
        if ((Common::getOption($lang, 'hide_language_' . $part))) {
            Config::remove('hide_language_' . $part, $lang);
            return true;
        }
        return false;
    }

}

$page = new CAdminLang("", $g['tmpl']['dir_tmpl_administration'] . "language.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>