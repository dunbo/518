<?php

/*
** 各种点击操作
*/

include_once (dirname(realpath(__FILE__)).'/weixin_init.php');

$exit_data = 0;

if (!$actsid) {
	exit(json_encode($exit_data));
}

$log_key = $_POST['log_key'];

$log_data = array();

if ($log_key == 'weixin_gomarket_download') {//端外的安智市场下载点击
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'actsid' => $_GET['actsid'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => $log_key
	);
	$exit_data = 0;
} else if ($log_key == 'weixin_share') {//端外的分享点击
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'actsid' => $_GET['actsid'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => $log_key
	);
	$exit_data = 0;
} else if ($log_key == 'weixin_answer') {//端外的查看答案点击
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'actsid' => $_GET['actsid'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => $log_key
	);
	$exit_data = 0;
}

permanentlog($activity_log_file, json_encode($log_data));
exit(json_encode($exit_data));