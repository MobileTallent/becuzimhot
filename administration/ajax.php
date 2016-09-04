<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$cmd = get_param('cmd');
$file = get_param('file');
$fileUserPart = Common::getOption('url_files', 'path') . 'tmpl/' . Common::getOption('main', 'tmpl');
$allowed = array('get_url_background_tmpl',
                 'get_url_main_page_image_urban',
                 'get_url_footer_tile_image_urban',
                 'get_url_footer_image_urban',
                 'get_url_background_profile',
                );
if (in_array($cmd, $allowed)) {
    if ($file != '') {
        if ($cmd == 'get_url_main_page_image_urban') {
            $dir = 'images/main_page_image/';
            $addTmpl = '_main_page_image_';
        } elseif ($cmd == 'get_url_footer_tile_image_urban') {
            $dir = 'images/footer_tiles/';
            $addTmpl = '_footer_tile_image_';
        } elseif ($cmd == 'get_url_footer_image_urban') {
            $dir = 'images/footer_image/';
            $addTmpl = '_footer_image_';
        } elseif ($cmd == 'get_url_background_profile') {
            $dir = 'images/patterns/';
            $addTmpl = '_profile_background_';
        } else {
            $dir = 'images/backgrounds/';
            $addTmpl = '_bg_';
        }
        $fileTmplUrl = Common::getOption('url_tmpl_main', 'tmpl') . $dir;
        $fileTmpl = "{$fileTmplUrl}{$file}";
        if (file_exists($fileTmpl)) {
            die($fileTmpl);
        }
        $fileUser = "{$fileUserPart}{$addTmpl}{$file}";
        if (file_exists($fileUser)) {
            die($fileUser);
        } else {
            die('no_file');
        }
    }

} elseif ($cmd == 'get_url_image_main_page') {
    if ($file != '') {
        if ($file == 'default') {
            die(Common::getOption('url_tmpl_main', 'tmpl') . 'images/main_page_dating_bg.png');
        }
        $fileUser = "{$fileUserPart}_main_page_dating_bg_user_{$file}";
        if (file_exists($fileUser)) {
            die($fileUser);
        } else {
            die('no_file');
        }
    }
} elseif ($cmd == 'timezone') {
    global $p;
    $pCurrent = $p;
    $p = 'options.php';
    $time = array('time_utc' => gmdate('Y-m-d H:i:s'),
                  'time_local' => TimeZone::getDateTimeZone(get_param('zone')));
    echo lSetVars('info_timezone', $time);
    $p = $pCurrent;
    die();
} elseif($cmd=='set_languages_order') {
	if(IS_DEMO) {
		die();
	}
    $part = get_param('part', 'main');

    $languagesOrder=array();
    if(isset($_POST['order_lang']) && is_array($_POST['order_lang'])){
        foreach($_POST['order_lang'] as $k=>$v){
            $languagesOrder[$v]=$k;
        }
    }

    if(isset($g['lang_order'][$part])){
        Config::update('lang_order',$part, serialize($languagesOrder));
    } else {
        Config::add('lang_order',$part, serialize($languagesOrder));
    }

} elseif ($cmd == 'check_code_youtube') {
    $codes = get_param_array('codes');
    $errors = 'error';
    if ($codes) {
        $errors = '';
        foreach ($codes as $code) {
            $error = 'available';
            $formats = array('mp4');//, 'webm'
            $links = @file_get_contents('http://www.youtube.com/get_video_info?video_id=' . $code . '&el=detailpage');
            if ($links === false) {
                $error = 'error_code';
            } else {
                foreach ($formats as $format) {
                    $infoVideo = checkCodeYouTubeVideoToDownload($code, $format, false, $links);
                    if (isset($infoVideo['error_code'])) {
                        $error = 'error_code';
                    }
                }
            }
            $errors .= $errors ? '&' . $error : $error;
        }
        echo $errors;
    } else {
        echo 'error';
    }
}

include("../_include/core/administration_close.php");
