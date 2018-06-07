<?php

include_once (dirname(realpath(__FILE__)).'/double11_promotion_init.php');

// 记日志
$log_data = array(
	'actsid' => $actsid,
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	"users" => '',
    "uid" => '',
    'key' => 'promotion_share_soft'
);
permanentlog($activity_log_file, json_encode($log_data));

// 设置此用户今天分享过
$data = array(
	'share_date' => $today,
);
$redis->sethash($actsid, $data, $r_cache_time);

echo 0;
exit;