<?php

// 圣诞节端外推广页
require_once(dirname(realpath(__FILE__)) . '/christmas2015_init.php');

$actsid = get_key("christmas2015:{$aid}");

// 判断活动是否已结束
if ($now > $activity_end_time) {
	// 推广页的结束页与端内结束页不一样，少了查看我的奖品
	$tplObj->display('lottery/christmas2015/christmas2015_promotion_end.html');
	exit;
}

$ignite_love = $redis->gethash($actsid, 'ignite_love');
$ignite_share = $redis->gethash($actsid, 'ignite_share');
$ignite_status = 0;
if ($ignite_love == $today) {
	$ignite_status |= 1;
}
if ($ignite_share == $today) {
	$ignite_status |= 2;
}

// 记日志
$log_data = array(
	'actsid' => $actsid,
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'promotion_show_homepage'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->out['ignite_status'] = $ignite_status;
$tplObj->out['actsid'] = $actsid;
$tplObj->display('lottery/christmas2015/christmas2015_promotion_index.html');
exit;