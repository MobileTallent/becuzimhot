<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CPage extends CHtmlBlock {

    public $image = null;
    public $user = null;

    function init() {
        $pid = ipar('id');
        if ($pid > 0) {
            $this->image = DB::row("SELECT * FROM gallery_images WHERE id = " . to_sql($pid, "Number") . " LIMIT 1");
        }
        if (!isset($this->image) or ! is_array($this->image)) {
            redirect('alb_albums.php');
        } else {
            $this->user = DB::row("SELECT `user_id`,`name` FROM `user` WHERE user_id = " . to_sql($this->image['user_id'], "Number") . " LIMIT 1");
            $this->image['url'] = DB::row("SELECT folder FROM gallery_albums WHERE id = " . to_sql($this->image['albumid'], "Number") . " LIMIT 1");
        }
    }

    function action() {

        if (get_param("cmd") == "delete") {
            $pid = ipar('id');
            $uid = ipar('uid');
            Gallery::imageDelete($pid, $uid);
            redirect("alb_albums_show.php");
        }
        if (get_param("cmd") == "update") {
            $pid = ipar('id');
            $data['title'] = get_param('title');
            $data['desc'] = get_param('text');
            DB::update('gallery_images',$data,"`id` = ".  to_sql($pid,"Number") ."");
            redirect("alb_albums_show.php");
        }

    }

    function parseBlock(&$html) {
        $this->image['title'] = he($this->image['title']);
        $this->image['desc'] = he($this->image['desc']);
        $this->image['url'] = 'gallery/images/' . $this->user['user_id'] . '/' . $this->image['url']['folder'] . '/' . $this->image['filename'];
        $html->setvar('img_url', $this->image['url']);
        $html->setvar('img_user_name', $this->user['name']);
        $html->setvar('img_user_id', $this->image['user_id']);
        $html->setvar('img_title_current', $this->image['title']);
        $html->setvar('img_id', $this->image['id']);
        $html->setvar('img_title', $this->image['title']);
        $html->setvar('img_desc', $this->image['desc']);

        parent::parseBlock($html);
    }

}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "alb_images_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
