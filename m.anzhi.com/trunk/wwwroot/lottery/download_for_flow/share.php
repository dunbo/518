<?php
/*
** 活动分享
*/
require_once(dirname(realpath(__FILE__)).'/init.php');
// 记录分享按钮日志
$log_data = array(
	'imsi' => $imsi,
	'sid' => $sid,
	'device_id' => $_SESSION['DEVICEID'],
	'time' => $now,
	'activity_id' => $aid,
	'key' => 'share_soft'
);
permanentlog($activity_log_file, json_encode($log_data));
echo 1; //记录日志成功