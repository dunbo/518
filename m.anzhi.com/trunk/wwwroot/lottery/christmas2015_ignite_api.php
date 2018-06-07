<?php

// 圣诞节端外推广页点亮圣诞树接口
require_once(dirname(realpath(__FILE__)) . '/christmas2015_init.php');

if (!$imsi_status) {
	echo -1;exit;
}

$action = $_GET['action'];
$lottery_num = get_lottery_num();

$ignite_love = $redis->get($rkey_imsi_ignite_love);
$ignite_share = $redis->get($rkey_imsi_ignite_share);

$flag = false;

if ($action == 1 && $ignite_love != $today) {
	$redis->set($rkey_imsi_ignite_love, $today, $r_cache_time);
	$flag = true;
	$ignite_love = $today;
} else if ($action == 2 && $ignite_share != $today) {
	$redis->set($rkey_imsi_ignite_share, $today, $r_cache_time);
	$flag = true;
	$ignite_share = $today;
}

$ignite_status = 0;
if ($ignite_love == $today) {
	$ignite_status |= 1;
}
if ($ignite_share == $today) {
	$ignite_status |= 2;
}

if ($flag) {
	// 状态有更新，判断ignite_status是否全了
	if (($ignite_status&1) && ($ignite_status&2)) {
		// 可抽奖次数+1
		$lottery_num += 1;
		set_lottery_num($lottery_num);
	}
}

// 记日志
$log_key = 'share_love';
if ($action == 2) {
	$log_key = 'share_soft';
}

$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	"users" => '',
    "uid" => '',
    'key' => $log_key
);
permanentlog($activity_log_file, json_encode($log_data));

echo $ignite_status;
exit;