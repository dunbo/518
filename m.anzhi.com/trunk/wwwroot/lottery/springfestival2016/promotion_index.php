<?php

require_once(dirname(realpath(__FILE__)) . '/init.php');

$actsid = get_key("springfestival2016:{$aid}");

// 判断活动是否已结束
if ($now > $activity_end_time) {
	// 推广页的结束页与端内结束页不一样，少了查看我的奖品
	$tplObj->display('lottery/springfestival2016/promotion_end.html');
	exit;
}

// 判断用户是否分享过
$share_day = $redis->gethash($actsid, 'share_day');
$ever_shared = 0;
if (!empty($share_day)) {
	$ever_shared = 1;
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
    'key' => 'promotion_index'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->out['actsid'] = $actsid;
$tplObj->out['ever_shared'] = $ever_shared;
$tplObj->display('lottery/springfestival2016/promotion_index.html');
exit;