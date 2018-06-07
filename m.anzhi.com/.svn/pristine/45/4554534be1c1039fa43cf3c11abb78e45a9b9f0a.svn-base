<?php
/*
** 活动分享接口，返回可抽奖次数
*/

include_once (dirname(realpath(__FILE__)).'/octoberflight_init.php');

if (!$imsi_status) {
	echo -1;
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

// 获得用户可抽奖次数（todo, 只从缓存中获得？）
$lottery_num = get_lottery_num();

// 判断用户今天是否分享过
$last_share_day = $redis->get($rkey_imsi_share_info);
if (!empty($last_share_day) && $last_share_day == $today) {
	echo $lottery_num;
	exit;
}

// 今天没分享过，可抽奖次数加1
$redis->set($rkey_imsi_share_info, $today, $r_cache_time);
$lottery_num++;
set_lottery_num($lottery_num);

echo $lottery_num;
exit;
