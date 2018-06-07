<?php

// 圣诞节端外推广页点亮圣诞树接口
require_once(dirname(realpath(__FILE__)) . '/christmas2015_init.php');

$actsid = get_key("christmas2015:{$aid}");

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