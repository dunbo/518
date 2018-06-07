<?php
/*
** 活动分享接口
*/
require_once(dirname(realpath(__FILE__)).'/init.php');
brush_second_do($aid, 1); //同一秒操作进黑名单函数
// 记录分享按钮日志
$log_data = array(
		'imsi' => $imsi,
		'sid' => $sid,
		'device_id' => $_SESSION['DEVICEID'],
		'time' => time(),
		'activity_id' => $aid,
		'key' => 'share_soft'
	);
permanentlog($activity_log_file, json_encode($log_data));
echo 1; //记录日志成功