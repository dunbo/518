<?php

require_once(dirname(realpath(__FILE__)) . '/liaoning_telecom_flux_init.php');

if ($end_status) {
	// 活动已结束
	$tplObj->display('lottery/liaoning_telecom_flux_end.html');
	exit;
}

if (!$imei_status) {
	// session失效
	$tplObj->display('lottery/liaoning_telecom_flux_hint.html');
	exit;
}

// 记录日志
$log_data = array(
	'imsi' => $_SESSION['USER_IMSI'],
	'imei' => $_SESSION['USER_IMEI'],
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid ,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'key' => 'index'
);
permanentlog('activity_'.$aid .'.log', json_encode($log_data));

if (!$new_status) {
	// 不是新注册用户
	$tplObj->display('lottery/liaoning_telecom_flux_hint.html');
	exit;
}

if ($get_flux_status) {
	// 已经参加过
	$tplObj->display('lottery/liaoning_telecom_flux_get_hint.html');
	exit;
} else {
	$tplObj->display('lottery/liaoning_telecom_flux_index.html');
	exit;
}