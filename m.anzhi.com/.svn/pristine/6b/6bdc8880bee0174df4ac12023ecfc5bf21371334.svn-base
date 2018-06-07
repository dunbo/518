<?php
/*
** 活动首页
*/
include_once(dirname(realpath(__FILE__)).'/init_page.php');

// 记录活动首页日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'show_homepage'
);
permanentlog($activity_log_file, json_encode($log_data));

if ($now > $a_end_time) {
	$tplObj->display('lottery/forfather_201706/end.html');
	exit;
} else {
	$tplObj->display('lottery/forfather_201706/game.html');
	exit;
}