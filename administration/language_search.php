<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminLangEdit extends CHtmlBlock {

    function parseBlock(&$html)
    {
        global $g;

        $fromTemplate = get_param('from_template');
        $html->setvar('from_template', $fromTemplate);

        $message = '';
        $search = trim(get_param('search', ''));
        $isEmptySearch = false;

        if ($search == '') {
            //$message = l('No results');
            $isEmptySearch = true;
        }

        $part = get_param('part', 'main');
        $html->setvar('part', $part);

        $langDir = Common::langPath($part, Common::getOption('dir_lang', 'path'));
        $langPart = $part;

        $lang = get_param('lang', 'default');
        $html->setvar('lang_this', $lang);

        $lang_page = get_param('lang_page', 'all');

        $langPath = $langDir . $langPart . '/' . $lang . '/language.php';
        $langDefault = $langDir . $langPart . '/default/language.php';

        $result = array();

        //if ($message == '') {

            $currentLang=array();
            if($lang!=='default' && file_exists($langDefault)){
                include($langPath);
                $currentLang=$l;
                $l=array();
                include($langDefault);
                
            }
        
            if (file_exists($langPath)) {
                include($langPath);

                if($fromTemplate) {
                    $l = wordsFromTemplate($l, $part);
                }

                ksort($l);

                foreach ($l as $k => $v) {
                    //echo $k . '<br>';
                    if (($isEmptySearch && $k == $lang_page)
                         || !$isEmptySearch) {
                    ksort($v);
                    foreach ($v as $key => $value) {
                        #$valueSearch = mb_strtolower($value, 'UTF-8');
                        #$search = mb_strtolower($search, 'UTF-8');
                        if ($isEmptySearch) {
                            $result[$k][$key] = $value;
                        } else {
                            $valueSearch = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                            if (mb_stripos($valueSearch, $search, 0, 'UTF-8') !== false) {
                                $result[$k][$key] = $value;
                            }
                        }
                    }
                    }
                }

                if (count($result)) {
                    $keyLetter = array();
                    foreach ($result as $section => $item) {
                        $html->setvar('section_title', ucfirst($section));
                        $html->setvar('lang_page_this', $section);
                        $html->setvar('lang_page_this_id', str_replace('.', '_', $section));
                        foreach ($item as $key => $value) {
                            $html->setvar('field_title', str_replace('_', ' ', ucfirst($key)));
                            $html->setvar('field', $key);
                            $html->setvar('field_key', $key);
                            $html->setvar('value', $value);
                            $html->parse('result');
                            $letter = substr(mb_strtolower($key, 'UTF-8'), 0, 1);
                            $keyLetter[] = $letter;
                            $html->setvar("letter", $letter);
                            $html->setvar("field", $key);
                            $html->setvar("value", $value);
                            $html->parse("textarea", false);

                            if (mb_strlen($value, 'UTF-8') > 150 || mb_strpos($value, "\n", 0, 'UTF-8') !== false)  {
                                $html->setvar('class_field', 'textarea');
                            } else {
                                $html->setvar('class_field', '');
                            }
                            
                            if($lang!='default' && !isset($currentLang[$section][$key])){
                                $html->setvar('delete_style', 'display:none;');
                            } else {
                                $html->setvar('delete_style', '');
                            }
                            
                            $html->parse("field", true);
                        }
                        $html->parse('section');
                        $html->setblockvar('result', '');
                        $html->setblockvar('field', '');
                    }
                    $keyLetter = array_unique($keyLetter);
                    $alphabet = array_merge(range(0, 9), range('A','Z'), array('ALL'));
                    foreach($alphabet as $l) {
                        $html->setvar("abc", $l);
                        $key = mb_strtolower($l, 'UTF-8');
                        if (in_array($key, $keyLetter) || $key == 'all') {
                            if ($key == 'all') {
                               $html->setvar("class_separator", 'separator_lf');
                               $html->parse("li_all", false);
                            }
                            $html->setvar("id_abc", $key);
                            $html->setvar("title_abc", $l);
                            $html->setblockvar("no_value", '');
                            $html->parse("yes_value", false);
                         } else {
                            $html->setblockvar("yes_value", '');
                            $html->parse("no_value", false);
                        }
                        if ($key == 8) {
                            $html->setvar("class_separator", 'separator_rg');
                        } elseif ($key != 'all') {
                            $html->setvar("class_separator", '');
                        }
                        $html->parse("alphabet", true);
                    }
                } else {
                    $message = l('No results');
                }
            } else {
                $message = l('Incorrect language file');
            }
        //}

        if ($message != '') {
            $html->setvar('message', $message);
            $html->parse('message');
        } else {
            $html->parse('results');
        }

        parent::parseBlock($html);
    }

}

$page = new CAdminLangEdit("", $g['tmpl']['dir_tmpl_administration'] . "language_search.html");

include("../_include/core/administration_close.php");
?>