<?php

/*
** 活动首页
*/

require_once(dirname(realpath(__FILE__)) . '/init_page.php');

// 判断是否活动是否已结束
if ($now > $activity_end_time) {
	// 跳转到活动结束页
	header("location:{$activity_end_url}");
	exit;
}

// 记日志
$log_data = array(
    'imsi' => $_SESSION['USER_IMSI'],
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'index'
);
permanentlog($activity_log_file, json_encode($log_data));
$tplObj -> out['is_test'] = $configs['is_test'];
$tplObj->display('lottery/commentreply_201704/index.html');
exit;