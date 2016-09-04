<?php
/* (C) ABK-Soft Ltd., 2004-2006
IMPORTANT: This is a commercial software product
and any kind of using it must agree to the ABK-Soft
Ltd. license agreement.
It can be found at http://abk-soft.com/license.doc
This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{
	function action()
	{
        global $g;

        $error = '';
        $ajax = get_param('ajax');
        $cmd = get_param('cmd', '');
        $title = trim(get_param('title', ''));
		$lang = get_param('lang', 'default');
        $key = get_param('key');

        $langDir = Common::langPath('main', $g['path']['dir_lang']);
        $filename = $langDir . 'main/'. $lang . '/language.php';
        if ($cmd == 'update')
		{
			include($filename);
            if ($title == '') {
                unset($l['all'][$key]);
            } else {
                $l['all'][$key] = $title;
            }
			$to = "";
			$to .= "<?php\r\n";
			foreach ($l as $k => $v) {
                foreach ($v as $k2 => $v2) {
                        $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
                }
				$to .= "\r\n";
			}
			$to = substr($to, 0, strlen($to) - 2);
			$to .= "?>";
            if (is_writable($filename)) {
                if (!$handle = @fopen($filename, 'w')) {
                    $error .= "Can\'t open file (" . $filename . ").\\r\\n";
                } else {
                    if (@fwrite($handle, $to) === FALSE)
                        $error .= "Can\'t write to file(" . $filename . ".).\\r\\n";
                    else {
                        @fclose($handle);
                        if ($ajax) {
                            die('updated');
                        }
                    }
                    @fclose($handle);
                }
            } else $error .= "Can\'t open file (" . $filename . ").\\r\\n";
            if($ajax) {
                echo $error;
                die();
            }
		}
	}

	function parseBlock(&$html)
	{
		global $g;

        $error = '';
        $table = '';
        $lang = get_param('lang', 'default');
        $html->setvar('lang', $lang);
        $mode = get_param('mode', 'country');
        $where = '';
        $modeDb = '';
        $href = '';
        $isField = true;

        if ($mode == 'city') {
            $modeDb = 'city';
            $href = '_cities';
            $where = '';
        } elseif ($mode == 'state') {
            $modeDb = 'state';
            $href = '_states';
        } elseif ($mode == 'country') {
            $modeDb = 'country';
            $href = '_countries';
        } elseif ($mode == 'fields') {
            $modeDb = 'fields';
            $href = '';
        }

        if ($modeDb != '') {
            if ($modeDb != 'fields') {
                $country_id = get_param('country_id');
                if ($country_id != '') {
                    $where = 'country_id = ' . to_sql($country_id, 'Number');
                    $country_options = DB::db_options("SELECT `country_id`, `country_title`
                                                         FROM `geo_country`
                                                        ORDER BY `country_title` ASC", $country_id);
                    $html->setvar('geo_countries', $country_options);
                    $html->parse('select_country');
                }

                $state_id = get_param('state_id');
                if ($state_id != '' && $country_id != '') {
                    $where = 'state_id = ' . to_sql($state_id, 'Number');
                    if ($country_id == '') {
                        $country_id = DB::result('SELECT `country_id` FROM `geo_country` ORDER BY `country_title` ASC LIMIT 1');
                    }
                    $state_options = DB::db_options("SELECT `state_id`, `state_title`
                                                       FROM `geo_state` WHERE `country_id` = $country_id
                                                      ORDER BY `state_title` ASC", $state_id);
                    $html->setvar('geo_states', $state_options);
                    $html->parse('select_state');
                }

                if ($state_id != '' || $country_id != '') {
                    $html->parse('select_block_localization');
                    $html->setvar('mode_js', $modeDb);
                    $html->parse('select_localization_js');
                }
                $rows = DB::select('geo_' . $modeDb, $where, $modeDb . '_title');
            } else {
                $table = get_param('table', 'const_orientation');
                $name  = str_replace(array('const_', 'var_'), '', $table);
                $table = UserFields::getField($name, 'table');
                $type = UserFields::getField($name, 'type');
                $rows = array();
                $isField = ($table !== null || in_array($type, array('text', 'textarea', 'location_map', 'appearance_group')));
                if ($isField) {
                    $field = (in_array($type, array('text', 'textarea', 'map', 'location', 'group'))) ? $name : $table;
                    $html->setvar('table', $field);
                    $title = UserFields::getField($name, 'title');
                    $html->setvar('table_title', ucfirst(str_replace('_',  ' ', $title)));
                    $html->parse('fields');
                    //$isTable = DB::row("SHOW TABLES LIKE '{$table}'");
                    if (!empty($table)) {
                        $rows = DB::select($table, '', '`id`');
                    }
                    array_unshift($rows, array('id' => 0, 'title' => $name));
                } else {
                    $error = l('field_not_exists');
                }
            }
            $html->setvar('href', $href);
            $html->setvar('mode', $modeDb);

            $l = array();

            $filename = Common::langPath('main', $g['path']['dir_lang']) . 'main/'. $lang . '/language.php';
            if (file_exists($filename)) {
                include($filename);
            } else {
                $error = "Can\'t open file (" . $filename . ").";
            }
            if ($isField) {
                $html->setvar('select_options_language', adminLangsSelect('main', $lang));
                $html->parse('language');
            }

            foreach ($rows as $row) {
                if ($modeDb != 'fields') {
                    $id = $modeDb . '_id';
                    $title = $modeDb . '_title';
                } else {
                    $id = 'id';
                    $title = 'title';
                }
                $html->setvar('id', $row[$id]);
                $title = $row[$title];
                if ($modeDb == 'fields' &&  $row[$id] == 0) {
                    $html->setvar('title', l('name_of_field'));
                } else {
                    $html->setvar('title', $title);
                }
                $key = to_php_alfabet($title);
                $html->setvar('key', $key);
                $titleLang = '';
                if (isset($l['all'][$key])) {
                    $titleLang = $l['all'][$key];
                }
                $html->setvar('title_lang', he(he_decode($titleLang)));

                $html->parse('item', true);
            }
        } else {
            $error = l('translate_type_is_not_set');
        }
        if ($error != '') {
            $html->setvar('message', $error);
            $html->parse('alert_message');
        }

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "users_fields_localization.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$mode = get_param('mode', 'country');
$typeP = '';
if ($mode == 'city') {
    $typeP = '_cities';
} elseif ($mode == 'state') {
    $typeP = '_states';
} elseif ($mode == 'country') {
    $typeP = '_countries';
}
$currentP = 'users_fields_localization.php';
$p = "users_fields{$typeP}.php";
$page->add(new CAdminPageMenuUsersFields());
$p = $currentP;

include("../_include/core/administration_close.php");