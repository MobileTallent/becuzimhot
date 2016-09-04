<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminFields extends CHtmlBlock {

    function write_to_users($option, $value, $position = 'max')
    {
        $position = Config::add('user_var', $option, '', $position);
        //$value['id'] = DB::insert_id();
        $value = serialize($value);
        Config::update('user_var', $option, $value);

        return $position;
    }

    function action()
    {
        global $g;
        $cmd = get_param("cmd", "");
        $table = get_param("table", "");
        #print_r($_POST);
        $name = get_param("name", "");

        $name_sql = to_sql(preg_replace('/ {1,}/', '_', trim(preg_replace("/[^a-z0-9\s]/", " ", strtolower($name)))), "Plain");



        if ($cmd != "") {
            $fieldsExists = array('user_id', 'user_editor_xml', 'user_search_filters');
            if (isset($g['user_var'][$name]) || isset($g['user_var'][$name_sql]) || in_array($name_sql, $fieldsExists))
                return false;
        }

        if ($cmd == "text") {
            $name = get_param("name", "");
            $group = get_param("group", 0);
            // remove bad characters from field name
            $maxlen = get_param("maxlen", "");
            if ((int) $maxlen == 0 or $maxlen > 255)
                $maxlen = 255;

            if ($name != "") {
                DB::execute("ALTER TABLE userinfo ADD " . to_sql($name_sql, "Plain") . " VARCHAR(" . to_sql($maxlen, "Number") . ") NOT NULL;"); {
                    DB::execute("ALTER TABLE texts ADD " . to_sql($name_sql, "Plain") . " VARCHAR(" . to_sql($maxlen, "Number") . ") NOT NULL;");
                    $w = "\$g['user_var']['" . $name_sql . "'] = array(";
                    $w .= "\"text\", \"" . $maxlen . "\", \"" . $name . "\"";
                    $w .= ");";

                    $value = array('text', $maxlen, $name, 'status' => 'active', 'type' => 'text', 'length' => $maxlen, 'title'=>$name, 'group'=> $group, 'number_values' => 0);
                    $this->write_to_users($name_sql, $value);
                    redirect('users_fields.php?table=' . $name_sql . '&fields=' . $name . '&action=saved#' . $name_sql);
                }
            }
        } elseif ($cmd == "textarea") {
            $name = get_param("name", "");
            $maxlen = get_param("maxlen", "");
            if ((int) $maxlen == 0 or $maxlen > 65000)
                $maxlen = 65000;

            if ($name != "") {

                if (DB::execute("ALTER TABLE userinfo ADD " . to_sql($name_sql, "Plain") . " TEXT NOT NULL;")) {
                    DB::execute("ALTER TABLE texts ADD " . to_sql($name_sql, "Plain") . " TEXT NOT NULL;");
                    $w = "\$g['user_var']['" . $name_sql . "'] = array(";
                    $w .= "\"textarea\", \"" . $maxlen . "\", \"" . $name . "\"";
                    $w .= ");";
                    $value = array('textarea', $maxlen, $name, 'status' => 'active', 'type' => 'textarea', 'length' => $maxlen, 'title'=>$name, 'group'=> 0, 'number_values' => 0);
                    $this->write_to_users($name_sql, $value);
                    redirect('users_fields.php?table=' . $name_sql . '&fields=' . $name . '&action=saved#' . $name_sql);
                }
            }
        } else {

            $name = get_param("name", "");
            $group = doubleval(get_param("group", 3));

            if ($name != "") {

                if ($name_sql == '') {
                    redirect();
                }
                $position = 'max';
                $isInt = true;
                if (DB::execute("CREATE TABLE var_" . to_sql($name_sql, "Plain") . " (
                                     id int(11) NOT NULL AUTO_INCREMENT ,
                                  title varchar(255) NOT NULL default '',
                                PRIMARY KEY (id)) ENGINE = MYISAM;"))
                {
                    DB::execute("ALTER TABLE userinfo ADD " . to_sql($name_sql, "Plain") . " INT(11) NOT NULL;");
                    $w = "\$g['user_var']['" . to_sql($name_sql, "Plain") . "'] = array(";
                    $w .= "\"from_table\", \"int\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"" . $group . "\"";
                    $w .= ");";


                    if ($cmd == "select_and_partner_checks") {
                        $w .= "\r\n\$g['user_var']['p_" . to_sql($name_sql, "Plain") . "'] = array(";
                        $w .= "\"from_table\", \"checks\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"2\"";
                        $w .= ");";


                        $value = array('from_table', 'checks', 'var_' . to_sql($name_sql, 'Plain'), $name, $group, 'status' => 'active',
                            'type' => 'checks', 'table'=> 'var_' . to_sql($name_sql, 'Plain'), 'title'=>$name, 'group'=> $group, 'number_values' => 0);
                        $position = $this->write_to_users('p_' . $name_sql, $value);

                        DB::execute("ALTER TABLE userpartner ADD p_" . to_sql($name_sql, "Plain") . " BIGINT(22) UNSIGNED NOT NULL;");
                    } elseif ($cmd == "checkbox" || $cmd == "radio") {
                        $isInt = false;
                        $value = array('type'          => 'checkbox',
                                       'table'         => 'var_' . to_sql($name_sql, 'Plain'),
                                       'title'         => $name,
                                       'status'        => 'active',
                                       'group'         => 1,
                                       'number_values' => 0);
                        $this->write_to_users($name_sql, $value);

                    } elseif ($cmd == "select_and_partner_interval") {
                        $w .= "\r\n\$g['user_var']['p_" . to_sql($name_sql, "Plain") . "_from'] = array(";
                        $w .= "\"from_table\", \"int\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"" . $name . " from\", \"from\", \"2\"";
                        $w .= ");\r\n";



                        $w .= "\$g['user_var']['p_" . to_sql($name_sql, "Plain") . "_to'] = array(";
                        $w .= "\"from_table\", \"int\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"" . $name . " to\", \"to\", \"2\"";
                        $w .= ");";
                        DB::execute("ALTER TABLE userpartner ADD p_" . to_sql($name_sql, "Plain") . "_from INT(11) NOT NULL;");


                        DB::execute("ALTER TABLE userpartner ADD p_" . to_sql($name_sql, "Plain") . "_to INT(11) NOT NULL;");


                        $value = array('from_table', 'int', 'var_' . to_sql($name_sql, 'Plain'), $name, $name . ' from', 'from', $group, 'status' => 'active',
                            'type' => 'from', 'table'=> 'var_' . to_sql($name_sql, 'Plain'), 'title'=>$name, 'group'=> $group, 'number_values' => 0);
                        $this->write_to_users('p_' . $name_sql . '_from', $value);

                        $value = array('from_table', 'int', 'var_' . to_sql($name_sql, 'Plain'), $name, $name . ' to', 'to', $group, 'status' => 'active',
                            'type' => 'to', 'table'=> 'var_' . to_sql($name_sql, 'Plain'), 'title'=>$name, 'group'=> $group, 'number_values' => 0);
                        $position = $this->write_to_users('p_' . $name_sql . '_to', $value);
                    }

                    if ($isInt) {
                        $value = array('from_table',
                                       'int',
                                       'var_' . to_sql($name_sql, 'Plain'),
                                       $name,
                                       $group,
                                       'status' => 'active',
                                       'type'   => 'int',
                                       'table'  => 'var_' . to_sql($name_sql, 'Plain'),
                                       'title'  => $name,
                                       'group'  => $group,
                                       'number_values' => 0);
                        $this->write_to_users($name_sql, $value, $position);
                    }

                    redirect('users_fields.php?table=' . $name_sql . '&fields=' . $name_sql . '&action=saved#' . $name_sql);
                }
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;

        if (get_param('cmd') != '') {
            $html->parse('finish', true);
        } else {
            $mod = Common::getOption('fields_mode', 'template_options');
            $mod = (empty($mod)) ? '' : "_{$mod}";
            $type = get_param('type');

            $optionsSelectGroup = array(
                '1' => l('fields_section_1'),
                '2' => l('fields_section_2'),
                '3' => l('fields_section_3'),
            );

            if($mod) {
                $optionsSelectGroup = array(
                    '1' => l('fields_section_1_urban'),
                    '2' => l('fields_section_2_urban'),
                );
            }
            $html->setvar('options_select_group', h_options($optionsSelectGroup, 1));

            if ($type == '') {
                if ($html->blockexists("select_type_item{$mod}")) {
                    $html->parse("select_type_item{$mod}", true);
                }
                $html->parse('select_type', true);
            } else {
                $section = "{$type}_section{$mod}";
                if ($html->blockexists($section)) {
                    $html->parse($section);
                }
            }

            $html->parse($type);
        }

        parent::parseBlock($html);
    }

}

$page = new CAdminFields("", $g['tmpl']['dir_tmpl_administration'] . "users_fields_add.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");
?>
