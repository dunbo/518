<?php
/*
** 活动首页
*/
require_once(dirname(realpath(__FILE__)).'/init_page.php');

// 判断活动是否已结束
if (time() > $a_end_time) {
	// 跳转到活动结束页
	header("location:{$a_end_url}");
	exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $sid,
    'time' => time(),
    'key' => 'index'
);
permanentlog($activity_log_file, json_encode($log_data));
$tplObj->display('lottery/commentreply_201707/index.html');