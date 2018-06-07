<?php

require_once(dirname(realpath(__FILE__)) . "/init.php");

$ret = array(
	'status' => -1
);

if (!$imsi_status) {
	$ret['status'] = -1;
	exit(json_encode($ret));
}

$lottery_num = get_lottery_num();
$share_day = $redis->get($rkey_imsi_share);
if ($share_day != $today) {
	// 今天还没有分享过
	$redis->set($rkey_imsi_share, $today, $r_cache_time);
	// 可抽奖次数+1
	$lottery_num += 1;
	set_lottery_num($lottery_num);
}

$ever_shared = 1;

$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	"users" => '',
    "uid" => '',
    'key' => 'share_soft'
);
permanentlog($activity_log_file, json_encode($log_data));

$ret = array(
	'status' => 200,
	'ever_shared' => $ever_shared,
	'lottery_num' => $lottery_num
);

exit(json_encode($ret));