<?php

// 端外推广页分享记日志
require_once(dirname(realpath(__FILE__)) . '/init.php');

$actsid = get_key("springfestival2016:{$aid}");

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
    'key' => 'promotion_go_lottery_click'
);
permanentlog($activity_log_file, json_encode($log_data));

echo 0;
exit;