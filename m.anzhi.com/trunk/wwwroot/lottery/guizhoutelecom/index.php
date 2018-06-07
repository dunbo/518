<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

// 判断活动是否已过期
if ($now > $activity_end_time) {
	$tplObj->display('lottery/guizhoutelecom/end.html');
	exit;
}

// 判断SESSION是否失效
if (!$imei_status) {
	$tplObj->display('lottery/guizhoutelecom/hint.html');
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

// 判断是否为新用户
if (!$new_status) {
	$tplObj->display('lottery/guizhoutelecom/hint.html');
	exit;
}

$tplObj->display('lottery/guizhoutelecom/index.html');

