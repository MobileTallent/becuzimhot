<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminFields extends CHtmlBlock
{
	public $message;

    private $table;
    private $table_name;
    private $field_name;
    private $is_table;
    private $allowTypeImage = array('png', 'gif', 'jpg', 'jpeg');


	/*function del_from_users($w)
	{
		global $g;
		$w = strtolower($w);

        Config::remove('user_var', $w);
        Config::remove('user_var', 'p_' . $w);
        Config::remove('user_var', 'p_' . $w . '_to');
        Config::remove('user_var', 'p_' . $w . '_from');
	}*/

    function uploadIcon($fields, $isMobile = false)
	{
        global $g;

        if (!$this->isAllowedUploadIcon($fields, $isMobile)) {
            return;
        }
        $height = 16;
        $width = 16;
        if ($isMobile && ($fields == 'i_am_here_to' || $fields == 'orientation')) {
            $height = 51;
            $width = 70;
        }
        $saveUrl = $this->getUrlTmplMobile($isMobile);
        $typeUploadIco = UserFields::getTypeUploadIcoField($isMobile);

        foreach ($typeUploadIco[$fields] as $type) {
            $typeFiles = $type . ($isMobile ? '_mobile' : '');
            if (isset($_FILES[$typeFiles])) {
                    $icons = $_FILES[$typeFiles];
                    foreach ($icons['tmp_name'] as $id => $icon) {
                        if (empty($icon) && $id == -1 && DB::insert_id()) {
                            $icon = $g['path']['dir_tmpl'].'common/images/1px.png';
                        }
                        if (!empty($icon) && Image::isValid($icon)) {
                            if ($id == -1) {
                                $id = DB::insert_id();
                            }
                            $image = new uploadImage($icon);
                            if ($image->uploaded) {
                                $image->file_safe_name = false;
                                $image->image_resize = true;
                                $image->image_ratio = true;
                                $image->image_convert = 'png';
                                $image->png_compression = 0;
                                $image->image_y = $height;
                                $image->image_x = $width;
                                $image->file_new_name_body = UserFields::getArrayNameIcoField($fields, $id, $type, $isMobile);
                                $image->file_new_name_ext = 'png';
                                $image->Process($saveUrl);
                                if (!$image->processed) {
                                    #echo '  Error: ' . $image->error . '';
                                }
                                unset($image);
                            }
                        }
                    }
            }
        }
    }

    function uploadIconAll($fields)
	{
        $this->uploadIcon($fields);
        $this->uploadIcon($fields, true);
    }

    function deleteIcon($field, $id, $isMobile = false)
	{
        if ($this->isAllowedUploadIcon($field, $isMobile)) {
            $url = $this->getUrlTmplMobile($isMobile);
            $icoNames = UserFields::getArrayNameIcoField($field, $id, '', $isMobile);
            foreach ($icoNames as $name) {
                @unlink("{$url}{$name}.png");
            }
        }
    }

    function deleteIconAll($fields, $id)
	{
        $this->deleteIcon($fields, $id);
        $this->deleteIcon($fields, $id, true);
    }

	function action()
	{
		global $g;
        global $DB_res;
        global $p;

		$cmd = get_param("cmd", "");
		$table = get_param("table", "");
        $fields = get_param('fields', '');


        if ($cmd == "saveposition") {

            UserFields::updateAllPosition($fields);
            die();

        } elseif ($cmd == "visible"){

            if ($option = 'relation') {
                $value = ($g['user_var']['relation']['status'] == 'active') ? 'N' : 'Y';
                Config::update('options', 'status_relation', $value);
            }
            UserFields::updateStatus($fields);
            die();

        } elseif ($cmd == "update") {
            $fieldsType = UserFields::getField($fields, 'type');
            $fildsId = UserFields::getField($fields, 'id');

			if ($table != "const_orientation")
			{
				$field = get_param_array('id');

				foreach ($field as $k => $v)
				{
					if ($k != -1 and $v != "")
					{
						DB::execute("UPDATE " . $table . " SET title=" . to_sql($v, "Text") . " WHERE id=" . to_sql($k, "Number") . "");
					}
					elseif ($k != -1 and $v == "")
					{
						if ($fieldsType == 'checkbox') {
							$where = '`field` = ' . to_sql($fildsId, 'Number') . ' AND `value` = ' . to_sql($k, 'Number');
							DB::delete('users_checkbox', $where);
						}
						DB::execute("DELETE FROM " . $table . " WHERE id=" . to_sql($k, "Number") . "");
                        $this->deleteIconAll($fields, $k);
					}
					elseif ($v != "")
					{
						DB::execute("INSERT INTO " . $table . " SET title=" . to_sql($v, "Text") . "");
					}
				}
			} else {
				$field = get_param_array("id");
				$s = get_param_array("search");
				$gs = get_param_array("gender");
				$f = get_param_array("free");
                $defaultId = get_param("default");
                //var_dump($default);
				foreach ($field as $k => $v)
				{
					if ($k != -1 and $v != "")
					{
                        $default = intval($k == $defaultId);
						DB::execute("
							UPDATE " . $table . " SET
							`title` =" . to_sql($v, "Text") . ",
							`search` =" . to_sql($s[$k], "Text") . ",
							`gender` =" . to_sql($gs[$k], "Text") . ",
							`free` =" . to_sql($f[$k], "Text") . ",
                            `default` = " . to_sql($default, "Number") . "
							WHERE id=" . to_sql($k, "Number") . "
						");
					} elseif ($k != -1 and $v == ""){
                        $this->deleteIconAll($fields, $k);
						DB::execute("DELETE FROM " . $table . " WHERE id=" . to_sql($k, "Number") . "");
					} elseif ($v != "") {
						DB::execute("INSERT INTO " . $table . " SET title=" . to_sql($v, "Text") . "");#, search=" . to_sql($s[$k], "Text") . "
					}

				}
			}
            $this->uploadIconAll($fields);
            UserFields::updateNumberValue($fields);
            Config::updateSiteVersion();
            redirect("users_fields.php?action=saved&table=" . $table . '&fields=' . $fields);

		} elseif ($cmd == 'delete') {
            $fieldsType = UserFields::getField($fields, 'type');
            $fildsId = UserFields::getField($fields, 'id');
			if (substr($this->table, 0, 6) != "const_") {
                if($table == '') {
                    $table = $fields;
                }

				$field = str_replace(' ', '_', strtolower(str_replace('var_', '', $table)));
                $field = to_sql($field, 'Plain');

				if ($fieldsType == 'checkbox') {
					$where = '`field` = ' . to_sql($fildsId, 'Number');
					DB::delete('users_checkbox', $where);
				}

                Config::remove('user_var', $field);
                Config::remove('user_var', 'p_' . $field);
                Config::remove('user_var', 'p_' . $field . '_to');
                Config::remove('user_var', 'p_' . $field . '_from');

                $columns = array(
                    'userinfo' => $field,
                    'texts' => $field,
                    'userpartner' => "p_{$field}",
                    'userpartner' => "p_{$field}_from",
                    'userpartner' => "p_{$field}_to",
                );
                foreach ($columns as $table => $column) {
                    if (UserFields::isColumnInTable($table, $column)) {
                        DB::execute("ALTER TABLE {$table} DROP {$column}", false);
                    }
                }
                DB::execute("DROP TABLE IF EXISTS var_" . $field, false);
				/*DB::execute("ALTER TABLE userinfo DROP " . $field, false);
				DB::execute("ALTER TABLE texts DROP " . $field, false);
				DB::execute("DROP TABLE var_" . $field, false);
				DB::execute("ALTER TABLE userpartner DROP p_" . $field, false);
				DB::execute("ALTER TABLE userpartner DROP p_" . $field . "_from", false);
				DB::execute("ALTER TABLE userpartner DROP p_" . $field . "_to", false);*/
			}
            redirect();
		}
	}
    function parseAllBlock($html, $items)
    {
        $optionTmplSet = Common::getOption('set', 'template_options');
        foreach ($items as $k => $v)
        {
            if (substr($k, 0, 2) != "p_")
            {
                $html->setvar("table_fields_position", $k);
                $html->setvar("table_this_url", $k);

                //if ($v[0] == 'from_table' || $v[0] == 'from_array')
                //{
                    // spread the field types
                    if (in_array($v['type'], array('int', 'const', 'radio', 'selectbox', 'checkbox', 'group', 'map', 'location', 'interests')))
                    {
                        $html->setvar("type_" . $v['group'], l('field_'.$v['type']));
                        if ($k == $this->table_name) $this->field_name = $v['title'];

                        $html->setvar("table_this_" . $v['group'], ($v['table']) ? $v['table'] : $k);
                        $html->setvar("table_title_this_" . $v['group'], l($v['title']));
                        $html->setvar("table_status_" . $v['group'], $k);
                        $html->setvar("type_fields_" . $v['group'], $v['type']);

                        if ($v['table'] == $this->table || $this->table == $v['type'])
						{
                            //$this->is_table = true;
							$html->parse("bb_" . $v['group'], false);
                            $html->parse("be_" . $v['group'], false);
						} else {
							$html->setblockvar("bb_" . $v['group'], "");
                            $html->setblockvar("be_" . $v['group'], "");
						}

                        $html->setvar("status_" . $v['group'], ($v['status'] == 'active') ? l('hide') : l('show'));
                        if ($v['type'] == 'const' || ($v['table'] != '' && strpos($v['table'], 'const') !== false)) {
                            /*$blockAction = 'action_visible';
                            if ($optionTmplSet == 'urban') {
                                $html->parse($blockAction . '_no', false);
                            } else {
                                $html->parse($blockAction, false);
                            }*/
                            if ($v['table'] == '') {// 'from_array' - Age range
                                $html->setblockvar("ba_" . $v['group'], false);
                                $html->parse("bn_" . $v['group'], '');
                            } else {
                                $html->setblockvar("bn_" . $v['group'], false);
                                $html->parse("ba_" . $v['group'], '');
                            }
                        }
                        $html->parse("table_" . $v['group'], true);
                    //}
                } elseif ($v['type'] == "text" || $v['type'] == "textarea") {
                    if ($k == $this->table_name) $this->field_name = $v['title'];

                        $html->setvar("table_this_" . $v['group'], $k);
                        //$html->setvar("table_this_id_" . $v['group'], $k);//'id_' .
                        $html->setvar("table_title_this_" . $v['group'], l($v['title']));
                        $html->setvar("type_" . $v['group'], l($v['type']));
                        $html->setvar("type_fields_" . $v['group'], $v['type']);
                        if ($k == $this->table_name)
						{
                            $html->parse("bb_" . $v['group'], false);
                            $html->parse("be_" . $v['group'], false);
						} else {
							$html->setblockvar("bb_" . $v['group'], "");
                            $html->setblockvar("be_" . $v['group'], "");
						}
                        $html->setvar("status_" . $v['group'], ($v['status'] == 'active') ? l('hide') : l('show'));
                        $html->setvar("table_status_" . $v['group'], $k);
                        $html->parse("table_" . $v['group'], true);
                }
            }
        }
    }

    function isAllowedUploadIcon($field, $isMobile = false)
	{
        global $g;

        if (!in_array($field, array('i_am_here_to', 'interests', 'orientation'))) {
            return false;
        }
        $option = 'upload_icon_field_' . $field;
        if (!$isMobile && !Common::isOptionActive($option, 'template_options')) {
            return false;
        }
        if ($isMobile && !isOptionActiveLoadTemplateSettings($option, null, 'mobile', $g['tmpl']['mobile'])) {
            return false;
        }

        return true;
    }

    function parseIcon(&$html, $id, $block = 'field_upload_ico', $isMobile = false)
	{
        global $g;

        if (!$this->isAllowedUploadIcon($this->table_name, $isMobile)) {
            return;
        }
        $typeUploadIco = UserFields::getTypeUploadIcoField($isMobile);

        foreach ($typeUploadIco[$this->table_name] as $type) {
            $icoName = UserFields::getArrayNameIcoField($this->table_name, $id, $type, $isMobile) . '.png';

            $url = $this->getUrlTmplMobile($isMobile) . $icoName ;
            if (file_exists($url)) {
                $html->setvar('rand', rand(0, 100000));
                $html->setvar('url_ico', $url);
            } else {
                $html->setvar('url_ico', $g['tmpl']['url_tmpl_administration'] .  'images/empty.png');
            }
            if ($type == 'search' || $type == 'category_selected') {
                $html->parse($block . '_dark', false);
            } else {
                $html->clean($block . '_dark');
            }
            $html->setvar($block . '_type', $type . ($isMobile ? '_mobile' : ''));
            $html->setvar($block . '_title', l($type . '_ico_' . ($isMobile ? 'mobile_' : '') . $this->table_name));
            $html->parse($block, true);
        }
    }



    function parseIconAll(&$html, $id, $block = 'field_upload_ico')
    {
        $html->clean($block);
        $this->parseIcon($html, $id, $block);
        $this->parseIcon($html, $id, $block, true);
    }

    function getUrlTmplMobile($isMobile)
    {
        global $g;

        $urlMobile = $g['path']['url_tmpl'] . 'mobile/' . $g['tmpl']['mobile'] . '/';
        return ($isMobile ? $urlMobile : $g['tmpl']['url_tmpl_main']) . 'images/';
    }

	function parseBlock(&$html)
	{
		global $g;
		global $p;

        if (get_param('action') == 'saved' && get_param('table')) {
            $html->parse('scroll_save');
            $fld = get_param('fields');
            if ($fld != '') {
                $html->setvar('id_save', $fld);
                $html->parse('action_save');
            }
        }

        $mod = Common::getOption('fields_mode', 'template_options');
        $mod = (empty($mod)) ? '' : "_{$mod}";
        $blockJs = "sort_table_js{$mod}";
        if ($html->blockexists($blockJs)) {
            $html->parse($blockJs, true);
        } else {
            $html->parse('sort_table_js', true);
        }
        for ($i = 0; $i < 4; $i++) {
            $section = 'fields_section_' . $i;
            $html->setvar($section, l($section . $mod));
        }


        UserFields::removeUnavailableField();

        $default = UserFields::getField('orientation', 'table');
		$this->table = get_param('table', empty($default) ? 'const_looking' : 'const_orientation');//no? - ???
        /*if ($this->table == '') {
            $this->table = 'const_orientation';
        }*/

        $this->table_name = str_replace(array('const_', 'var_'), '', $this->table);
        $this->table = UserFields::getField($this->table_name, 'table');

        $type = UserFields::getField($this->table_name, 'type');

        if (in_array($type, array('text', 'textarea'))) {
            $this->table = $this->table_name;
        }

        if (empty($this->table)) {
            $this->table = $type;
        };

        $html->setvar('table', $this->table);
        $html->setvar('table_title_id', $this->table);
        $html->setvar('table_fields', get_param('fields', 'orientation'));


        if (isset($g['user_var']) and is_array($g['user_var'])) {
            $this->parseAllBlock($html, $g['user_var']);
        }

        if (!empty($this->table)) {

            $html->setvar('table_lang', (in_array($this->table, array('text', 'textarea', 'group', 'map', 'location'))) ? $this->table_name : $this->table);
            $lang = get_param('lang');
            $html->setvar('select_options_language', adminLangsSelect('main', $lang));
            $html->parse('language');

            $html->setvar('table_title', l($this->field_name));

            if (!in_array($type, array('text', 'textarea', 'map', 'location', 'group')) && DB::query("SELECT * FROM " . $this->table . " ORDER BY id"))
            {

                //$isUploadIcon = in_array($this->table, array('const_i_am_here_to', 'const_interests'));
                //$typeUploadIco = UserFields::getTypeUploadIcoField();

                if (!in_array($this->table, array('var_star_sign'))) {
                    $this->parseIconAll($html, '', 'block_add_upload_ico');
                    /*if ($isUploadIcon) {
                        $block = 'block_add_upload_ico';
                        $this->parseIcon($html, '', $block);
                        foreach ($typeUploadIco[$this->table_name] as $type) {
                            $html->setvar($block . '_type', $type);
                            $html->setvar($block . '_title', l($type . '_ico_' . $this->table_name));
                            $html->parse($block, true);
                        }
                    }*/
                    $html->parse('block_add', true);
                    $html->setvar('field_input_disabled', '');
                } else {
                    $html->setvar('field_input_disabled', 'disabled');
                }
                if ($this->table != 'const_orientation') {
                    while ($row = DB::fetch_row()) {
                        $html->setvar('id', $row['id']);
                        $html->setvar('value', he($row['title']));
                        $this->parseIconAll($html, $row['id']);
                        /*$block = 'field_upload_ico';
                        if ($isUploadIcon) {
                            foreach ($typeUploadIco[$this->table_name] as $type) {
                                $icoName = UserFields::getArrayNameIcoField($this->table_name, $row['id'], $type) . '.png';
                                $url = $g['tmpl']['dir_tmpl_main'] . 'images/' . $icoName ;
                                if (file_exists($url)) {
                                    $html->setvar('rand', rand(0, 100000));
                                    $html->setvar('ico_name', $icoName);
                                } else {
                                    $html->setvar('ico_name', 'empty.png');
                                }
                                if ($type == 'search') {
                                    $html->parse($block . '_dark', false);
                                } else {
                                    $html->clean($block . '_dark');
                                }
                                $html->setvar($block . '_type', $type);
                                $html->setvar($block . '_title', l($type . '_ico_' . $this->table_name));
                                $html->parse($block, true);
                            }
                        }*/

                        $html->parse('field', true);
                        //$html->clean('field_upload_ico');
                    }
                    $html->parse("fields", true);
                } else {
                    if  (Common::getOption('set', 'template_options') != 'urban'){
                    $types = array('none' => l('None'),
                                   'silver' => l('Silver'),
                                   'gold' => l('Gold'),
                                   'platinum' => l('Platinum'),);
                    } else {
                    $types = array('none' => l('None'),
                                   'platinum' => l('Super Powers'),
                                  );
                    }
                    $default = DB::result('SELECT `id` FROM `const_orientation` WHERE`default` = 1', 0, 1);
                    $setDefault = false;
                    while ($row = DB::fetch_row()) {
                        $html->setvar('search_options', DB::db_options("SELECT `id`, `title` FROM `const_orientation` ORDER BY `id` ASC", $row['search'], 2));
                        $html->setvar("id", $row['id']);
                        $html->setvar("value", he($row['title']));
                        $this->parseIconAll($html, $row['id'], 'field_upload_ico_o');
                        //$html->setvar("search", $row['search']);
                        if ((!$default && $setDefault) || $default == $row['id']) {
                            $html->setvar('default_checked', 'checked');
                            $setDefault = false;
                        } else {
                            $html->setvar('default_checked', '');
                        }
                        if ($row['gender'] == 'M')
                            $html->setvar('gender_m', 'checked');
                        else
                            $html->setvar('gender_m', '');
                        if ($row['gender'] == 'F')
                            $html->setvar('gender_f', 'checked');
                        else
                            $html->setvar('gender_f', '');
                        if  ((Common::getOption('set', 'template_options') == 'urban')&&( $row['free'] != 'none')){
                            $row['free'] = 'platinum';
                        }
                        $html->setvar('select_free_access', h_options($types, $row['free']));
                        $html->parse('field_o', true);
                    }
                    $html->parse('fields', true);
                }
            } else {
                $html->parse('no_variables', true);
            }
            if (substr($this->table, 0, 6) != 'const_'
                && !in_array($type, array('group', 'map', 'location'))
                && !in_array($this->table, array('var_star_sign'))) {
                $html->parse('button_delete', true);
            }

        } else {
            $this->message .= l('field_not_exists');
        }

		$html->setvar('message_fields', $this->message);
		parent::parseBlock($html);
	}
}
$ajax = get_param("ajax", "");
if ($ajax){
    die();
}
$page = new CAdminFields("", $g['tmpl']['dir_tmpl_administration'] . "users_fields.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");

?>