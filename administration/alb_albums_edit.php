<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CPage extends CHtmlBlock {

    public $album = null;
    public $user = null;

    function init() {
        $pid = ipar('id');
        if ($pid > 0) {
            $this->album = DB::row("SELECT * FROM gallery_albums WHERE id = " . to_sql($pid, "Number") . " LIMIT 1");
        }
        if (!isset($this->album) or ! is_array($this->album)) {
            redirect('alb_albums.php');
        } else {
            $this->user = DB::row("SELECT `name` FROM `user` WHERE user_id = " . to_sql($this->album['user_id'], "Number") . " LIMIT 1");
        }
    }

    function action() {

        if (get_param("cmd") == "delete") {
            $pid = ipar('id');
            $uid = ipar('uid');
            Gallery::albumDelete($pid, $uid);
            redirect("alb_albums.php");
        }
        if (get_param("cmd") == "update") {
            $pid = ipar('id');
            $data['title'] = get_param('title');
            $data['desc'] = get_param('text');
            DB::update('gallery_albums', $data, "`id` = " . to_sql($pid, "Number") . "");
            redirect("alb_albums.php");
        }
    }

    function parseBlock(&$html) {
        $this->album['title'] = he($this->album['title']);
        $this->album['desc'] = he($this->album['desc']);

        $html->setvar('alb_user_name', $this->user['name']);
        $html->setvar('alb_user_id', $this->album['user_id']);
        $html->setvar('alb_title_current', $this->album['title']);
        $html->setvar('alb_id', $this->album['id']);
        $html->setvar('alb_title', $this->album['title']);
        $html->setvar('alb_desc', $this->album['desc']);

        parent::parseBlock($html);
    }

}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "alb_albums_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
