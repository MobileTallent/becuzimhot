<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = 'login';
include("../_include/core/administration_start.php");
if (Common::isMultisite()) {
    redirect('home.php');
}
class CAdminTemplate extends CHtmlBlock
{
	var $message_template = "";
    var $tmplOptionSet = "";

	function action()
	{
		global $g;
		global $p;
		global $area;

		$part = get_param("part", "main");
		$cmd = get_param("cmd", "");

        $tmplDir = Common::tmplPath($part, $g['path']['dir_tmpl']);
        $tmplPart = $part;

		if ($cmd == 'add_tmpl') {
			$title = mb_strtolower(trim(get_param('add_title', '')));
            $tmplPath = $tmplDir . $tmplPart . '/'. $title . '/';
			if ($title == '' or is_dir($tmplPath)) {
				$this->message_template .= 'This title already exists or you entered empty title.<br />';
			} else {
                $tmplSrc = Common::tmplPath($part, $g['tmpl']['dir_tmpl_' . $part]);
                if(!file_exists($tmplSrc)) {
                    $tmplSrc = Common::findExistsTmpl($part);
                }
                if($tmplSrc) {
                    Common::dirCopy($tmplSrc, $tmplPath);
                } else {
                    $this->message_template .= 'Source template not exists.<br />';
                }
                redirect($p . '?part=' . $part);
			}
		}

		$set = get_param('set', '');
        $tmplPath = $tmplDir . $tmplPart . '/'. $set . '/';
		if ($set != '' and is_dir($tmplPath)) {
            Config::update('tmpl', $part, $set);
            if ($part == 'main') {
                $tmplOptionMainSet = loadTemplateSettings('main', $set, 'set');
                $tmplOptionMobileSet = loadTemplateSettings('mobile', $g['tmpl']['mobile'], 'set');
                if ($tmplOptionMainSet != $tmplOptionMobileSet) {
                    $dir = Common::tmplPath($part, $g['path']['dir_tmpl']) . 'mobile/';
                    if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                            while (($file = readdir($dh)) !== false) {
                                if (is_dir($dir . $file) and substr($file, 0, 1) != ".") {
                                    if (loadTemplateSettings('mobile', $file, 'set') == $tmplOptionMainSet) {
                                        Config::update('tmpl', 'mobile', $file);
                                        break;
                                    }
                                }
                            }
                            closedir($dh);
                        }
                    }
                }
            }
            redirect("$p?part=$part");
		}

		$del = str_replace(array('/', '\\'), '', get_param('del', ''));
		$dir = $tmplDir . $tmplPart . '/'. $del . '/';
		if ($del != '' and is_dir($dir)) {
			@chmod($dir, 0777);
            Common::dirRemove($dir);
			if (file_exists($dir)) {
                $this->message_template .= "Access denied. Please delete manually({$dir}).<br />";
			} else {
				redirect($p. '?action=saved&part=' . $part);
			}
		}
	}

    function excludeNotAvailableTmpl($part, $tmpl)
	{
        if ($part == 'mobile') {
            $tmplOptionSet = loadTemplateSettings('mobile', $tmpl, 'set');
            return $tmplOptionSet != $this->tmplOptionSet;
        } else {
            return false;
        }

    }

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $p;
		global $area;

        $html->setvar("message_template", $this->message_template);

		$part = get_param("part", "main");
        $this->tmplOptionSet = Common::getOption('set', 'template_options');

        $dir = Common::tmplPath($part, $g['path']['dir_tmpl']);

		if (is_dir($dir))
		{
	   		if ($dh = opendir($dir))
	   		{
		        while (($file = readdir($dh)) !== false)
		        {
					if (is_dir($dir . $file) and substr($file, 0, 1) != ".")
					{
						if ($part == $file)
						{
							$html->setvar("part", $file);
							$html->setvar("title", ucfirst($file));
							$html->parse("part_on", false);
							$html->setblockvar("part_off", "");
						}
						else
						{
							$html->setvar("part", $file);
							$html->setvar("title", ucfirst($file));
							$html->parse("part_off", false);
							$html->setblockvar("part_on", "");
						}
						$html->parse("part", true);
					}
		        }
		        closedir($dh);
    		}
		}

		$html->setvar("part", $part);

        $dir = Common::tmplPath($part, $g['path']['dir_tmpl']);

        $dir .= $part . '/';

		$langs = array();
		if (is_dir($dir)) {
	   		if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
					if (is_dir($dir . $file) and substr($file, 0, 1) != ".") {
						$langs[$file] = array($file, ucfirst($file));
					}
		        }
		        closedir($dh);
    		}
		}

		sort($langs);

		foreach ($langs as $k => $v) {
            if ($this->excludeNotAvailableTmpl($part, $v[0])){
                //unset($langs[$k]);
                continue;  
                
            } 
            $html->setvar("tmpl_this", $v[0]);
            $html->setvar("title", $v[1]);
			$html->parse("mtmpl", true);
        }
        $i = 0;
        //$activeTmpl = Common::tmplPath($part, $g['tmpl']['dir_tmpl_' . $part]);
		foreach ($langs as $k => $v) {
            if ($this->excludeNotAvailableTmpl($part, $v[0])) {
                continue;
            }    
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

            $html->setvar("template", $v[0]);
            $html->setvar("title", $v[1]);

			if ($g['tmpl'][$part] == $v[0]) {
                $html->setvar('class_active', 'active');
                /*
                if(count($langs)==1 && $part!='main' && $part!='administration'){
                    $html->parse("delete_on", false);
                }
                */
				$html->parse("tmpl_on", false);
				$html->setblockvar("tmpl_off", "");
			} else {
                $html->setvar('class_active', '');
				$html->parse("tmpl_off", false);
				$html->setblockvar("tmpl_on", "");
			}
			$html->parse("tmpl", true);
		}


		parent::parseBlock($html);
	}
}

$page = new CAdminTemplate("", $g['tmpl']['dir_tmpl_administration'] . "template.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");