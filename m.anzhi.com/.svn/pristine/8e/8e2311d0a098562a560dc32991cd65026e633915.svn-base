<?php

// 圣诞节端外推广页点亮圣诞树接口
require_once(dirname(realpath(__FILE__)) . '/christmas2015_init.php');

$actsid = get_key("christmas2015:{$aid}");
$action = $_GET['action'];

// 设置用户的跳转url
if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
	$jump_url_host = 'http://118.26.203.23';
} else {
	$jump_url_host = 'http://promotion.anzhi.com';
}
$jump_url = $jump_url_host . '/lottery/christmas2015_lottery.php';

$ignite_love = $redis->gethash($actsid, 'ignite_love');
$ignite_share = $redis->gethash($actsid, 'ignite_share');

if ($action == 1 && $ignite_love != $today) {
	$arr = array(
		'ignite_love' => $today,
		'url' => $jump_url
	);
	$redis->sethash($actsid, $arr, $r_cache_time);
	$ignite_love = $today;
} else if ($action == 2 && $ignite_share != $today) {
	$arr = array(
		'ignite_share' => $today,
		'url' => $jump_url
	);
	$redis->sethash($actsid, $arr, $r_cache_time);
	$ignite_share = $today;
}

$ignite_status = 0;
if ($ignite_love == $today) {
	$ignite_status |= 1;
}
if ($ignite_share == $today) {
	$ignite_status |= 2;
}

// 记日志
$log_key = 'promotion_share_love';
if ($action == 2) {
	$log_key = 'promotion_share_soft';
}

$log_data = array(
	'actsid' => $actsid,//端外标识id
    'imsi' => $imsi,//端外无法获得此值，此值为空
    'device_id' => $_SESSION['DEVICEID'],//端外无法获得此值，此值为空
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],//端外无法获得此值，此值为空
    'time' => time(),
	"users" => '',
    "uid" => '',
    'key' => $log_key
);
permanentlog($activity_log_file, json_encode($log_data));

echo $ignite_status;
exit;