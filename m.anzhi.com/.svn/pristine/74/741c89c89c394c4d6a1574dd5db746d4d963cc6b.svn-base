<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');

session_begin();
if (!empty($_COOKIE['_AZ_COOKIE_'])) {
	$ucenter = new GoUcenter('www');
	$cookie_data = $ucenter->parse_uc_cookie();
	if (empty($_SESSION['USER_ID']) || $_SESSION['USER_ID'] != $cookie_data['pid']) {
		$user = $ucenter->token_userinfo();
		if (isset($user['USER_ID']) && isset($user['USER_NAME'])) {
			$_SESSION['USER_ID'] = $user['USER_ID'];
			$_SESSION['USER_UID'] = $user['USER_UID'];
			$_SESSION['USER_NAME'] = $user['USER_NAME'];
			$_SESSION['user_data'] = array(
				'login_account' => $cookie_data['loginAccount'],
				'user_name' => $user['USER_NAME'],
				'userid' => $user['USER_ID'],
			);
			//日志
			$log_data = array(
				'uid' => $user['USER_UID'],
			    'users' =>$user['USER_NAME'],
				'imsi' => $_SESSION['USER_IMSI'],
				'device_id' => $_SESSION['DEVICEID'],
				'activity_id' => $active_id,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $sid,
				'time' => time(),
				'key' => 'login'   
			);
			permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		} else {
			setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
			setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
		}
	}
} else {
	if (!empty($_SESSION['user_data'])) {
		unset($_SESSION['USER_ID']);
		unset($_SESSION['USER_UID']);
		unset($_SESSION['USER_NAME']);
		unset($_SESSION['user_data']);
	}
}
$activity_id = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];
$package = $_GET['package'];

$log_data = array(
	'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $activity_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'num' => 1,
	'package' => $package,
	'key' => 'download_soft'
);

permanentlog('activity_'.$activity_id.'.log', json_encode($log_data));

echo 200;
return 200;


