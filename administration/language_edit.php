<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$ajax = get_param('ajax');
if($ajax && IS_DEMO) {
    echo 'updated';
    die();
}

class CAdminLangEdit extends CHtmlBlock
{
	var $message_lang = "";
	function action()
	{
		global $g;

        $ajax = get_param('ajax');

		$cmd = get_param("cmd", "");
		$part = get_param("part", "main");
		$lang = get_param("lang", "default");
		$lang_page = to_php(get_param("lang_page", "all"));

        $langDir = Common::langPath($part, $g['path']['dir_lang']);
        $langPart = $part;


        $filename = $langDir . $langPart . '/'. $lang . '/language.php';
        $langDefault = $langDir . $langPart . '/default/language.php';

		if ($cmd == "update")
		{
            if($lang!=='default' && file_exists($langDefault)){
                include($langDefault);
                $defaultLang=$l;
                $l=array();
            }
			if(file_exists($filename)){
                include($filename);
            } else {
                $l=array();
            }

			for ($i = 1; $i <= 10; $i++)
			{
				if (to_php_alfabet(get_param("field" . $i, "")) != "")
				{
					$k = to_php_alfabet(get_param("field" . $i, ""));
					$v = get_param("new" . $i, "");
					$l[$lang_page][$k] = $v;
				}
			}
            $wordKey=get_param('word_key', "");
            if($lang!=='default' && $wordKey!=="" && 
                    !isset($l[$lang_page][$wordKey]) && 
                    isset($defaultLang[$lang_page][$wordKey])){
                        
                if(!isset($l[$lang_page])){
                    $l[$lang_page]=array();
                }
                $l[$lang_page][$wordKey]=get_param($wordKey, "");
            }

			$to = "";
			$to .= "<?php \r\n";
			foreach ($l as $k => $v)
			{
				if ($k == $lang_page) foreach ($v as $k2 => $v2)
				{
					$field_name = ($k2=="submit") ? "submit_js_patch" : $k2;
					$to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php(get_param($field_name, 0) === 0 ? $v2 : get_param($field_name, "")) . "\";\r\n";
				}
				else foreach ($v as $k2 => $v2) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
				$to .= "\r\n";
			}
			$to = substr($to, 0, strlen($to) - 2);
			$to .= "?>";

			#@chmod($g['path']['dir_lang'] . $part . "/". $lang . "/language.php", 0777);

            //if (is_writable($filename)) {
                if (!$handle = @fopen($filename, 'w')) {
                    $this->message_lang .= "Can't open file (" . $filename . ").<br />";
                } elseif(is_writable($filename)) {
                    if (@fwrite($handle, $to) === FALSE)
                        $this->message_lang .= "Can't write to file(" . $filename . ".).<br />";
                    else {
                        @fclose($handle);
                        if ($ajax) {
                            echo 'updated';
                            die();
                        } else {
                            redirect("language_edit.php?action=saved&part=" . $part . "&lang=" . $lang . "&lang_page=" . $lang_page . '&from_template=' . get_param('from_template'));
                        }
                    }
                    @fclose($handle);
                }  else {
                    @fclose($handle);
                    $this->message_lang .= "Can't open file (" . $filename . ").<br />";
                }    
            //} else $this->message_lang .= "Can't open file (" . $filename . ").<br />";
            if($ajax) {
                echo $this->message_lang;
                die();
            }
		}
		elseif ($cmd == "delete")
		{
			include($filename);

			$key_del = get_param("key_del", "");

			if($key_del=="submit_js_patch") $key_del = "submit";

			$to = "";
			$to .= "<?php \r\n";
			foreach ($l as $k => $v)
			{
				if ($k == $lang_page)
				{
					foreach ($v as $k2 => $v2) if ($k2 != $key_del) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
				}
				else foreach ($v as $k2 => $v2) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
				$to .= "\r\n";
			}
			$to = substr($to, 0, strlen($to) - 2);
			$to .= "?>";

			#@chmod($g['path']['dir_lang'] . $part . "/". $lang . "/language.php", 0777);

			if (is_writable($filename))
			{
			    if (!$handle = @fopen($filename, 'w'))
			    {
			        $this->message_lang .= "Can't open file (" . $filename . ").<br />";
			    }
			    if (fwrite($handle, $to) === FALSE)
			    {
			        $this->message_lang .= "Can't write to file(" . $filename . ".).<br />";
			    }
			    else
			    {
					@fclose($handle);
                    if($ajax) {
                        $defaultWord='';
                        if($lang!=='default' && file_exists($langDefault)){
                            $l=array();
                            include($langDefault);
                            if(isset($l[$lang_page][$key_del])){
                                $defaultWord=$l[$lang_page][$key_del];
                            }
                        }
                        echo json_encode(array('message'=>'deleted','default_word'=>$defaultWord));
                        die();
                    } else {
                        redirect("language_edit.php?action=saved&part=" . $part . "&lang=" . $lang . "&lang_page=" . $lang_page);
                    }
			    }
		    	@fclose($handle);
			}
			else
			{
				$this->message_lang .= "Can't open file (" . $filename . ").<br />";
                
			}

            if($ajax) {
                echo $this->message_lang;
                die();
            }
		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $p;
        $html->setvar('from_template', get_param('from_template'));

		$part = get_param("part", "main");
		$html->setvar("part", $part);

        $langDir = Common::langPath($part, $g['path']['dir_lang']);
        $langPart = $part;


		$html->setvar("part_title", ucfirst($part));
		$lang = get_param("lang", "default");
		$html->setvar("lang_this", $lang);

        $langTitle = ucfirst($lang);
        if($lang == 'default') {
            $langTitle = 'English';
        }

		$html->setvar("lang_title_this", $langTitle);
		$lang_page = get_param("lang_page", "all");
		$html->setvar("lang_page_this", $lang_page);

        $html->setvar('lang_page_this_id', str_replace('.', '_', $lang_page));


		$dir = $langDir . $langPart . '/';
		$langs = array();
		if (is_dir($dir)) {
	   		if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
					if (is_dir($dir . $file) and substr($file, 0, 1) != '.') {

                        $langTitle = ucfirst($file);
                        if($file == 'default') {
                            $langTitle = 'English';
                        }

						$langs[$langTitle] = $file;
					}
		        }
		        closedir($dh);
    		}
		}

		natsort($langs);

        $langActive =  Common::getOption($part, 'lang_value');
        $langCurrentKey = array_search($langActive, $langs);

        if($langCurrentKey !== false) {
            $langCopy = $langs[$langCurrentKey];
            unset($langs[$langCurrentKey]);
            $langs = array($langCurrentKey => $langCopy) + $langs;
        }

		$l_glob = $l;
		unset($l);

        foreach ($langs as $k => $v) {
            $html->setvar("language", $v);
            $html->setvar("title", $k);
            $wordsCount = loadAndCountLanguageWords($v, $part);
            $html->setvar('words_count', $wordsCount);

            if ($v==$lang) {
				$html->parse("mlang_on", false);
			} else {
				$html->setblockvar("mlang_on", "");
			}
			$html->parse("mlang", true);
        }

        $langPath = $langDir . $langPart . '/'. $lang . '/language.php';
        $langDefault = $langDir . $langPart . '/default/language.php';
        /*
        if(!file_exists($langPath)){
            $fp = fopen($langPath, "w");
            fclose($fp);
        }
        */
		//if (file_exists($langPath))
        if(is_dir($langDir . $langPart . '/'. $lang)){
            $currentLang=array();
            if($lang!=='default' && file_exists($langDefault)){
                include($langPath);
                $currentLang=$l;
                $l=array();
                include($langDefault);
                
            }
			if (file_exists($langPath)){
                include($langPath);
            }    
            if(!isset($l)){
                $l=array();
            }
            
            if(get_param('from_template')) {
                $l = wordsFromTemplate($l, $part);
            }

            ksort($l);

            $wordsCount = wordsCountInLanguage($l);

			foreach ($l as $k => $v)
			{
				$ltitle = substr(ucfirst($k),0,12);
				if(strlen($k)>strlen($ltitle)) $ltitle=$ltitle."...";

				if ($k == $lang_page)
				{
					$html->setvar("page_title", ucfirst($k));
					$html->setvar("page", $k);

                    $lPart = array();
                    $lPart[$k] = $l[$k];
                    $html->setvar('words_count_section', wordsCountInLanguage($lPart));

                    $fieldIndex = 1;

                    ksort($v);
                    $keyLetter = array();
					foreach ($v as $k2 => $v2)
					{
						$field_name = $k2=="submit" ? $k2."_js_patch" : $k2;
                        
						$html->setvar("field", $field_name);
						$html->setvar("field_title", str_replace("_", " ", ucfirst($k2)));
                        $letter = substr(mb_strtolower($field_name, 'UTF-8'), 0, 1);
                        $keyLetter[] = $letter;
                        $html->setvar("letter", $letter);
                        $html->setvar("value", $v2);
                        if (strlen($v2) > 150 || strpos($v2, "\n") !== false)
						{
                            $html->setvar('class_field', 'textarea');
                        } else {
                            $html->setvar('class_field', '');
                        }

                        $html->setvar('field_index', $fieldIndex);
                        
                        if($lang!='default' && !isset($currentLang[$k][$k2])){
                            $html->setvar('delete_style', 'display:none;');
                        } else {
                            $html->setvar('delete_style', '');
                        }
                        
                        $fieldIndex++;
						$html->parse("field", true);
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

					$html->setvar("lang_page", $k);
                    $html->setvar('lang_page_id', str_replace('.', '_', $k));
					$html->setvar("lang_page_title", $ltitle);
					$html->parse("lang_on", false);
					$html->setblockvar("lang_off", "");
				}
				else
				{

					$html->setvar("lang_page", $k);
                    $html->setvar('lang_page_id', str_replace('.', '_', $k));
					$html->setvar("lang_page_title",$ltitle);
					$html->parse("lang_off", false);
					$html->setblockvar("lang_on", "");
				}
				$html->parse("lang", true);
			}
		}
		else
		{
			//$this->message_lang = "Incorrect language files.<br />";
            redirect("language.php");

		}

		$html->setvar("message_lang", $this->message_lang);
		unset($l);
		$l = $l_glob;

        $addCount = 5;
        for($addIndex = 1; $addIndex <= $addCount; $addIndex++) {
            $html->setvar('add_index', $addIndex);
            $html->parse('add');
        }

		parent::parseBlock($html);
	}
}

$page = new CAdminLangEdit("", $g['tmpl']['dir_tmpl_administration'] . "language_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>