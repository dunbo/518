<?php

// 点击分享按钮

require_once(dirname(realpath(__FILE__)) . '/timememory_init.php');

if (!$imsi_status) {
	echo 0;
	exit;
}

// 记日志
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

// 可抽奖次数
//$now_num = $redis->get($rkey_imsi_lottery_num);
$now_num = get_lottery_num();
// 分享信息
$last_share_day = $redis->get($rkey_imsi_share_info);
if (empty($last_share_day) || $last_share_day != $today) {
	$redis->set($rkey_imsi_share_info, $today, $r_cache_time);
	// 可抽奖次数+1
	$now_num += 1;
	//$redis->set($rkey_imsi_lottery_num, $now_num, $r_cache_time);
	set_lottery_num($now_num);
}
echo $now_num;
exit;