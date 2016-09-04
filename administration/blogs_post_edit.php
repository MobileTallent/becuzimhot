<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/blogs/includes.php");

class CPage extends CHtmlBlock
{
    public $post = null;
    function init()
    {
        $pid = ipar('id');
        if ($pid > 0) {
            $this->post = CBlogsTools::getPostById($pid, true);
        }
        if (!isset($this->post) or !is_array($this->post)) {
            redirect('blogs_posts.php');
        }
    }
	function action()
	{
		if (CBlogsTools::filterText(param('text')) != '') {
            CBlogsTools::updatePostByIdByAdmin($this->post['id']);
		 redirect("blogs_posts.php?action=saved");
		}
	}
	function parseBlock(&$html)
	{

        if ($this->post['images'] != '') {
            $imgs_orig = explode('|', $this->post['images']);
            $imgs = array();
            foreach ($imgs_orig as $k => $img_orig) {
                $imgs[$k]['i'] = $img_orig;
                $imgs[$k]['url'] = g('path', 'url_files') . 'blogs/' . $this->post['id'] . '_' . $img_orig . '_t.jpg';
                $imgs[$k]['file'] = g('path', 'url_files') . 'blogs/' . $this->post['id'] . '_' . $img_orig . '_o.jpg';
            }
            $html->items('img', $imgs);
            $html->parse('imgs');
        }
        $this->post['subject'] = he($this->post['subject']);
        $html->assign('post', array_merge($this->post, CBlogsTools::getPostFromPostNotNull()));
		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "blogs_post_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
