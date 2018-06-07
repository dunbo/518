<?php

include_once (dirname(realpath(__FILE__)).'/telecomplan_a_init.php');

// 判断活动是否已过期
if ($now > $activity_end_time) {
	// 跳转到结束页
	$tplObj->display('lottery/telecomplan_a_end.html');
	exit;
}

// 判断imei是否有效
if (!$imei_status) {
	$tplObj->out['hint_status'] = 1;
	// 跳转到imei的提示页
	$tplObj->display('lottery/telecomplan_a_hint.html');
	exit;
}

if (!$init_status) {
	$tplObj->out['hint_status'] = 2;
	$tplObj->display('lottery/telecomplan_a_hint.html');
	exit;
}

// 记日志
$log_data = array(
	'imsi' => $imsi,
	'imei' => $imei,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid ,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',		
	'key' => 'show_homepage'
);
permanentlog('activity_'.$aid .'.log', json_encode($log_data));

$tplObj->display('lottery/telecomplan_a_index.html');