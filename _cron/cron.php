<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['to_root'] = '../';
include($g['to_root'] . '_include/core/main_start.php');

$min = intval(date('i'));
$hour = intval(date('H'));

if ($min == 0) {
    Flashchat::clearHistory();
}

if ($min % 1 == 0) {
    //DB::execute("UPDATE user SET hide_time=(hide_time-10), last_visit=last_visit WHERE hide_time>0");

    if (!Common::isOptionActive('hide_profile_enabled')) {
            $sql = "UPDATE `user`
                       SET `hide_time` = 0
                     WHERE `hide_time` > 0";
			DB::execute($sql);
    }
    // use as online
    $onlineCount = trim($g['options']['use_as_online_count']);

    if ($onlineCount != 0) {
        $onlineRange = explode('-', $onlineCount);
        if(!isset($onlineRange[1])) {
            $count = intval($onlineRange[0]);
        } else {
            $count = rand(intval(trim($onlineRange[0])), intval(trim($onlineRange[1])));
        }

        if($count) {
            $timeDelta = 60 * (2 + $g['options']['online_time']);

            // update users
            $sql = 'UPDATE user
                SET last_visit = ' . to_sql(date('Y-m-d H:i:s', time() - $timeDelta), 'Text') . '
                WHERE hide_time = 0
                    AND use_as_online = 1';
            DB::execute($sql);

            // update only online members
            // +60 minutes if cron can runs only every 30 minutes or more
            $timeoutForCron = 60 * 60;
            $sql = 'UPDATE user
                SET last_visit = ' . to_sql(date('Y-m-d H:i:s', time() + $timeDelta + $timeoutForCron), 'Text') . '
                WHERE hide_time = 0
                    AND use_as_online = 1
                ORDER BY RAND() LIMIT ' . intval($count);
            DB::execute($sql);
        }
    }

    if(!IS_DEMO) {
        include_once(dirname(__FILE__) . '/../_include/current/match_mail.php');
        Match_Mail::run();
    }

    /*$lastDataPostWall = Common::getOption('last_date', 'event_wall_birthday');
    $currentDate = date('Y-m-d');
    if ($currentDate > $lastDataPostWall && date('H:i:s') >= '10:00:00') {
        $sql = 'SELECT `user_id`, `name` FROM `user` WHERE DATE_FORMAT(`birth`, "%m-%d") = ' . to_sql(date('m-d'), 'Text');
        $users = DB::rows($sql);
        foreach ($users as $user) {
            Wall::add('comment', 0, $user['user_id'], 'item_birthday', false, 0, 'friends');
        }
        Config::updateAll('event_wall_birthday', array('last_date' => $currentDate));
    }*/

}

if ($min % 10 == 0) {
    DB::delete('city_moving', '`created` < ' . to_sql((date('Y-m-d H:i:00', time() - 600))));
}

// Hourly
$timeCronHourly = Common::getOption('date','cron_hourly');

if (date('Y-m-d H')>$timeCronHourly){
    Config::update('cron_hourly', 'date', date('Y-m-d H'));

    $date = date('Y-m-d');
    $hour = intval(date('H'));

    if (Common::isEnabledAutoMail('end_paid')) {
        DB::query('SELECT * FROM user WHERE gold_days=1 AND payment_day<"'.$date.'" AND payment_hour='.$hour. '');
        while ($row = DB::fetch_row()) {
            $vars = array(
                'title' => $g['main']['title'],
                'name' => $row['name'],
            );
            Common::sendAutomail($row['lang'], $row['mail'], 'end_paid', $vars);
        }
    }
    // update only profiles that will be free now
    $data = array('set_hide_my_presence' => 2, 'set_do_not_show_me_visitors' => 2, 'type' => 'none');
    DB::update('user', $data, '`gold_days` = 1  AND payment_day<"'.$date.'" AND payment_hour='.$hour. '');

    DB::execute('UPDATE user SET gold_days=(gold_days-1), last_visit=last_visit WHERE gold_days>0  AND payment_day<"'.$date.'" AND payment_hour='.$hour. ';');

}

// Daily
$date = date('Y-m-d');
if ($hour == 0) {
    $timecron = Common::getOption('date','cron');
    if($timecron != $date) {
        Config::update('cron', 'date', $date);
        DB::update('user', array('sp_sending_messages_per_day' => 0));
    }
}

if ($hour == 10) {
    $lastDataPostWall = Common::getOption('last_date', 'event_wall_birthday');
    if ($date != $lastDataPostWall) {
        if ($lastDataPostWall === null) {
            Config::add('event_wall_birthday', 'last_date', 0, 'max', 0);
        }
        Config::update('event_wall_birthday', 'last_date', $date);
        $dateBirthday = date('m-d');
        $whereDate = '';
        if ($dateBirthday == '03-01' && !checkdate('02', '29', date('Y'))) {
            $whereDate = " OR DATE_FORMAT(`birth`, \"%m-%d\") = '02-29'";
        }
        $sql = 'SELECT `user_id`, `name`
                  FROM `user`
                 WHERE DATE_FORMAT(`birth`, "%m-%d") = ' . to_sql(date('m-d'))
                 . $whereDate;
        $users = DB::rows($sql);
        foreach ($users as $user) {
            Wall::add('comment', 0, $user['user_id'], 'item_birthday', false, 0, 'friends');
        }
    }
}

echo 'Cron works';

include($g['to_root'] . '_include/core/main_close.php');