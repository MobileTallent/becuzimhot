<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{
	var $message = "";
	var $login = "";
	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "update")
		{
	        $event_id = get_param('event_id');
	        DB::query("SELECT m.* ".
	            "FROM events_event as m ".
	            "WHERE m.event_id=" . to_sql($event_id, 'Number') . " LIMIT 1");
	        if($event = DB::fetch_row())
	        {
                      DB::query("SELECT m.*, cn.*, st.*, ct.* " .
                        "FROM events_event as m, geo_country as cn, geo_state as st, geo_city as ct " .
                        "WHERE m.event_id=" . to_sql($event_id, 'Number') . " AND m.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
                      if ($event = DB::fetch_row()) {
                            $category_id = get_param('category',$event['category_id']);
                            $city_id = get_param('city',$event['city_id']);
                            $event_private = get_param('event_private',$event['event_private']);
                            $event_title = get_param('event_title',$event['event_title']);
                            $event_description = get_param('event_description',$event['event_description']);
                            $event_date = get_param('event_date');
                            $event_time = get_param('event_time');
                            $event_address = get_param('event_address',$event['event_address']);
                            $event_place = get_param('event_place',$event['event_place']);
                            $event_site = get_param('event_site',$event['event_site']);
                            $event_phone = get_param('event_phone',$event['event_phone']);

                            $event_date = date("Y-m-d", strtotime($event_date));

                            DB::execute('UPDATE events_event SET ' .
                                    'category_id=' . to_sql($category_id) .
                                    ', city_id=' . to_sql($city_id) .
                                    ', event_private=' . to_sql($event_private) .
                                    ', event_title=' . to_sql($event_title) .
                                    ', event_description=' . to_sql($event_description) .
                                    ', event_datetime=' . to_sql($event_date . ' ' . $event_time) .
                                    ', event_address=' . to_sql($event_address) .
                                    ', event_place=' . to_sql($event_place) .
                                    ', event_site=' . to_sql($event_site) .
                                    ', event_phone=' . to_sql($event_phone) .
                                    ', updated_at=NOW() WHERE event_id=' . $event['event_id']);

                            redirect("events_event_edit.php?event_id=" . $event['event_id'] . "&action=saved");
                }
            }
        }
    }
	function parseBlock(&$html)
	{
		global $g;

        $event_id = get_param('event_id');
        DB::query("SELECT m.*, cn.*, st.*, ct.* ".
            "FROM events_event as m, geo_country as cn, geo_state as st, geo_city as ct ".
            "WHERE m.event_id=" . to_sql($event_id, 'Number') . " AND m.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
        if($event = DB::fetch_row())
        {
        	$html->setvar('user_id', $event['user_id']);
        	$html->setvar('event_id', $event['event_id']);
        	$html->setvar('event_private', $event['event_private']);
        	$html->setvar('event_title', he($event['event_title']));
            $html->setvar('event_date', date("m/d/Y", strtotime($event['event_datetime'])));
            $html->setvar('event_time', date("H:i", strtotime($event['event_datetime'])));
        	$html->setvar('event_description', $event['event_description']);
            $html->setvar('event_datetime', $event['event_datetime']);
        	$html->setvar('event_address', he($event['event_address']));
        	$html->setvar('event_place', he($event['event_place']));
        	$html->setvar('event_site', $event['event_site']);
        	$html->setvar('event_phone', he($event['event_phone']));
            $category_options = '';
            DB::query("SELECT * FROM events_category ORDER BY category_id");
            $lang = loadLanguageAdmin();
            while($category = DB::fetch_row())
            {
                $category_options .= '<option value=' . $category['category_id'] . ' ' . (($category['category_id'] == $event['category_id']) ? 'selected="selected"' : '') . '>';
                $category_options .= l($category['category_title'], $lang, 'events_category'); 
                $category_options .= '</option>';           
            }
            $html->setvar("category_options", $category_options);
/*
            $event_private_options = '<option value=0 ' . ((!$event['event_private']) ? 'selected="selected"' : '') . '>';
            $event_private_options .= l('public'); 
            $event_private_options .= '</option>';           
            $event_private_options .= '<option value=1 ' . (($event['event_private']) ? 'selected="selected"' : '') . '>';
            $event_private_options .= l('private'); 
            $event_private_options .= '</option>';           
            $html->setvar("event_private_options", $event_private_options);*/
            
            $html->setvar("country_options", DB::db_options("SELECT country_id, country_title FROM geo_country;", $event['country_id']));
            $html->setvar("state_options", DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id=" . $event['country_id'] . " ORDER BY state_title;", $event['state_id']));
            $html->setvar("city_options", DB::db_options("SELECT city_id, city_title FROM geo_city WHERE state_id=" . $event['state_id'] . " ORDER BY city_title;", $event['city_id']));
        
            DB::query("SELECT * FROM events_event_image WHERE event_id=" . $event['event_id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "events_event_images/" . $image['image_id'] . "_b.jpg");
                $html->setvar("image_id", $image['image_id']);
                $html->parse("image");
                
                $n_images++;
                
                $html->parse('photo');
            }
            
            $html->parse('photo_edit');

           if(empty($event['event_private'])) {
                $html->parse('event_private_off',false);
            }
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "events_event_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");