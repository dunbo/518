<?php

include_once (dirname(realpath(__FILE__)).'/double11_init.php');

if (!$imsi_status) {
	echo 0;exit;
}

// 日志
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

$redis->set($rkey_imsi_share_info, $today, $r_cache_time);
echo 1;
exit;