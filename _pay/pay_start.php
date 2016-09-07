<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

function payment_check_return($option, $type = '', $days = '')
{
    static $cache = array();

    if(Common::isOptionActive('free_site')) {
        return true;
    }

	if ($type == '') {
        $type = guser('type');
    }
	if ($days == '') {
        $days = guser('gold_days');
    }

    if ($type == 'membership' && Common::getOption('set', 'template_options') != 'urban') {
        $type = 'platinum';
    }

    $keyCheck = $option . '_' . $type . '_check';
    if(isset($cache[$keyCheck])) {
        $check = $cache[$keyCheck][0];
        $checkForAll = $cache[$keyCheck][1];
    } else {
        $option = to_sql($option);

        $sql = 'SELECT code FROM payment_type
            WHERE type = ' . to_sql($type) . '
                AND code = ' . $option;

        $check = DB::result($sql, 0, DB_MAX_INDEX);
        $cache[$keyCheck][0] = $check;

        $sql = 'SELECT code FROM payment_type
            WHERE code = ' . $option;

        $checkForAll = DB::result($sql, 0, DB_MAX_INDEX);
        $cache[$keyCheck][1] = $checkForAll;
    }

    if (!$checkForAll) {
        return true;
    }

    if ($check && $days > 0) {
        return true;
    } else {
        return false;
    }
}

function checkAccessFeatureByPayment($feature, $isCheckActiveModule = true)
{
    if ($isCheckActiveModule && !Common::isOptionActive($feature)) {
        redirect(Common::getHomePage());
    } else if (Common::getOption('set', 'template_options') == 'urban') {
        if (!User::accessСheckFeatureSuperPowers($feature)) {
            redirect('upgrade.php');
        }
    } else {
        payment_check($feature);
    }
}

function payment_check($option, $type = '', $days = '')
{
	global $g;

	if (!payment_check_return($option, $type, $days)) {
        if (guid() > 0) {
            redirect($g['to_root'] . "upgrade.php?option=" . $option);
        } else {
            Common::toLoginPage();
        }
	}
}

function upgrade_user($user_id, $item)
{
		global $g;
		DB::query("SELECT * FROM payment_plan WHERE item=" . $item . "");
		$row = DB::fetch_row();
        if ($row['type'] == 'credits') {
            DB::execute('UPDATE `user`
                            SET credits = credits + ' . to_sql($row['gold_days'], 'Number') . '
                          WHERE `user_id` = ' . to_sql($user_id, 'Number'));
        } else {
            $timeStamp=time()+3600;  //+60 minutes
            $date=date('Y-m-d',$timeStamp);
            $hour=intval(date('H',$timeStamp));
            DB::execute("UPDATE user
                         SET gold_days=" . to_sql($row['gold_days'], "Number") . ",
                         type=" . to_sql($row['type']) . ",
                         payment_day=".to_sql($date).",
                         payment_hour=".to_sql($hour)."
                         WHERE user_id=" . to_sql($user_id, "Number") . " ");

            User::upgradeCouple($user_id, $row['gold_days'], $row['type']);

            $price = $row['amount'];
            $partner = DB::result("SELECT partner FROM user WHERE user_id=" . to_sql($user_id, "Number") . "");
            $plus = ($price / 100) * $g['options']['partner_percent'];
            DB::execute("UPDATE partner SET
                         account=(account+" . to_sql($plus, "Number") . "),
                         summary=(summary+" . to_sql($plus, "Number") . "),
                         count_golds=(count_golds+1)
                         WHERE partner_id=" . $partner . " ");

            $p_partner = DB::result("SELECT p_partner FROM partner WHERE partner_id=" . $partner . "");
            $plus = ($price / 100) * $g['options']['partner_percent_ref'];
            DB::execute("UPDATE partner SET
                         account=(account+" . $plus . "),
                         summary=(summary+" . $plus . ")
                         WHERE partner_id='" . $p_partner . "'");
        }
}

function getTypePaymentPlan($item)
{
    $sql = 'SELECT `type` FROM `payment_plan` WHERE `item` = ' . to_sql($item);
    return DB::result($sql);
}

function insertBefore($data)
{
    $data['dt'] = date('Y-m-d H:i:s');
    $data['user_id'] = get_session('user_id');
    $data['type'] = get_param('type');
    //$data['item'] $data['system'] $data['code']
    DB::insert('payment_before', $data);
}

function updateBefore($data)
{
    //$data['item'] $data['system'] $data['code']

    $where = '`user_id` = ' . to_sql($data['user_id'], 'Number') .
             ' AND `item` = ' . to_sql($data['item'], 'Number') .
             ' AND `system` = ' . to_sql($data['system']) .
             ' AND `code` = ' . to_sql($data['code']);

    DB::update('payment_before', array('system' => $data['system'] . ' payed'), $where);
}

function getCode($item, $amount, $customResponse = '')
{
    $code = md5(md5(microtime()) . md5(rand(0, 100000)));
    $code = base64_encode($code . "-" . get_session('user_id') . '-' . $item . '-' . $amount . $customResponse);
    return $code;
}

function checkPlan()
{
    $item = get_param('item');
    $item = DB::result('SELECT `item` FROM `payment_plan` WHERE `item` = ' . to_sql($item));
    return (get_session('user_id') == '' || $item == 0) ? false : $item;
}

function optionsPlan($item)
{
    DB::query('SELECT * FROM `payment_plan` WHERE `item` = ' . to_sql($item, 'Number'));
    return DB::fetch_row();
}

function  optionsPayment($system)
{

    $custom = get_param('custom');
    $p = explode('-', base64_decode($custom));
    log_payment($p);

    $responseData = array();
	if (count($p) > 1) {
		$code = to_sql($custom, 'Plain');
		$user_id = $p[1];
        $item = $p[2];
		$cost = $p[3];

		$type = DB::result('SELECT `type`
                              FROM `payment_before`
                             WHERE `user_id` = ' . to_sql($user_id, 'Number') .
                             ' AND `item` = ' . to_sql($item, "Number") .
                             ' AND `system` = ' . to_sql($system) .
                             ' AND `code` = ' . to_sql($code));

        if ($type !== 0) {
            upgrade_user($user_id, $item);
        }

        $data = array('user_id' => $user_id,
                      'item' => $item,
                      'system' => $system,
                      'code' => $code);

        updateBefore($data);
        $responseData['type'] = $type;
        $responseData['item'] = $item;
	}
    return $responseData;
}

function getAfterHtml($action, $response)
{
    return '<html>
                <body>
                <form name="check_out_form" action="../../' . $action . '" method="post">
                <input type="hidden" name="cmd" value="thank">
                ' . $response . '
                <script language="JavaScript">document.forms["check_out_form"].submit();</script>
                </form>
                </body>
            </html>';
}

function log_payment($p)
{
	global $g;
	$data = "Varibles:\n{\n";
    if(is_array($p) && count($p)) {
        foreach ($p as $k => $v) {
            $data .= "\t" . $k . " => " . $v . "\n";
        }
    }
	$data .= "}\n\n";
	if (isset($_SERVER['HTTP_REFERER'])) {
		$data .= "From:\n{\n";$data .= "\t" . 'HTTP_REFERER' . " => " . $_SERVER['HTTP_REFERER'] . "\n";
		$data .= "}\n\n";
	}
	$data .= "GET:\n{\n";
	foreach ($_GET as $k => $v) {
		$data .= "\t" . $k . " => " . $v . "\n";
	}
	$data .= "}\n\n";
	$data .= "POST:\n{\n";
	foreach ($_POST as $k => $v) {
		$data .= "\t" . $k . " => " . $v . "\n";
	}
	$data .= "}\n\n";

	DB::execute("
		INSERT INTO payment_after SET
		dt='" . date("Y-m-d H:i:s") . "',
		data=" . to_sql($data, "Text") . "
	");
}

if (!function_exists('get_url')) {
	function get_url()
	{
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$request = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$request = $_SERVER['REQUEST_URI'];
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
			$request = $_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) {
				$request .= '?' . $_SERVER['QUERY_STRING'];
			}
		} else {
			$request = '';
		}
		return 'http://' . $_SERVER['HTTP_HOST'] . $request;
	}
}
