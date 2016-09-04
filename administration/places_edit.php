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
	        $id = get_param("id");
			DB::query("SELECT p.*, c.title, cn.*, st.*, ct.* ".
	            "FROM places_place as p, places_category as c, geo_country as cn, geo_state as st, geo_city as ct ".
	            "WHERE p.id=" . to_sql($id, 'Number') . " AND p.category_id = c.id AND ".
	            "p.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
	        if($place = DB::fetch_row())
	        {
				$category_id = get_param('category');
	            DB::query('SELECT * FROM places_category WHERE id = ' . to_sql($category_id, 'Number'));
	            if($category = DB::fetch_row())
	            {
	                $country_id = get_param('country');
	                DB::query('SELECT * FROM geo_country WHERE country_id = ' . to_sql($country_id, 'Number'));
	                if($country = DB::fetch_row())
	                {
	                    $state_id = get_param('state');
	                    DB::query('SELECT * FROM geo_state WHERE country_id = ' . $country['country_id'] . ' AND state_id = ' . to_sql($state_id, 'Number'));
	                    if($state = DB::fetch_row())
	                    {
	                        $city_id = get_param('city');
	                        DB::query('SELECT * FROM geo_city WHERE country_id = ' . $country['country_id'] . ' AND state_id = ' . $state['state_id'] . ' AND city_id = ' . to_sql($city_id, 'Number'));
	                        if($city = DB::fetch_row())
	                        {
	                            $name = get_param('place_name');
	                            $phone = get_param('place_phone');
	                            $site = get_param('place_site');
	                            $about = get_param('place_about');
	                            $address = get_param('place_address');

	                            if($name && $about)
	                            {
	                                DB::execute('UPDATE places_place SET category_id=' . $category['id'] .
	                                    ', name=' . to_sql($name) . ', phone=' . to_sql($phone) . ', site=' . to_sql($site) . ', about=' . to_sql($about) .
	                                    ', address=' . to_sql($address) . ', city_id=' . $city['city_id'] .
	                                    ', updated_at=NOW() WHERE id=' . $place['id']);

									global $p;
									redirect("$p?action=saved&id=".get_param("id"));

	                            }
	                        }
	                    }
	                }
	            }
	        }
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $id = get_param('id');
        DB::query("SELECT p.*, c.title, cn.*, st.*, ct.* ".
            "FROM places_place as p, places_category as c, geo_country as cn, geo_state as st, geo_city as ct ".
            "WHERE p.id=" . to_sql($id, 'Number') . " AND p.category_id = c.id AND ".
            "p.city_id = ct.city_id AND ct.state_id = st.state_id AND st.country_id = cn.country_id LIMIT 1");
        if($place = DB::fetch_row())
        {
        	$html->setvar('user_id', $place['user_id']);
        	$html->setvar('place_id', $place['id']);
        	$html->setvar('place_name', he($place['name']));
        	$html->setvar('place_phone', he($place['phone']));
        	$html->setvar('place_site', $place['site']);
        	$html->setvar('place_about', $place['about']);
        	$html->setvar('place_address', he($place['address']));

	        $category_options = '';
	        DB::query("SELECT * FROM places_category ORDER BY id");
            $lang = loadLanguageAdmin();
	        while($category = DB::fetch_row())
	        {
	            $category_options .= '<option value=' . $category['id'] . ' ' . (($category['id'] == $place['category_id']) ? 'selected="selected"' : '') . '>';
	            $category_options .= l($category['title'], $lang, 'places_category');
	            $category_options .= '</option>';
	        }
	        $html->setvar("category_options", $category_options);

	        $html->setvar("country_options", DB::db_options("SELECT country_id, country_title FROM geo_country;", $place['country_id']));
	        $html->setvar("state_options", DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id=" . $place['country_id'] . " ORDER BY state_title;", $place['state_id']));
	        $html->setvar("city_options", DB::db_options("SELECT city_id, city_title FROM geo_city WHERE state_id=" . $place['state_id'] . " ORDER BY city_title;", $place['city_id']));

            DB::query("SELECT * FROM places_place_image WHERE place_id=" . $place['id'] . " ORDER BY created_at ASC");
            $n_images = 0;
            while($image = DB::fetch_row())
            {
                $html->setvar("image_thumbnail", $g['path']['url_files'] . "places_images/" . $image['id'] . "_th.jpg");
                $html->setvar("image_file", $g['path']['url_files'] . "places_images/" . $image['id'] . "_b.jpg");
                $html->setvar("image_id", $image['id']);
                $html->parse("image");

                $n_images++;

                $html->parse('photo');
            }

            $html->parse('photo_edit');
        } else {
            redirect('places_results.php');
        }

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "places_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");