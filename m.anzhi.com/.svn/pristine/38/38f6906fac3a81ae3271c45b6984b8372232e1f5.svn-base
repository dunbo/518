<?php

include_once(dirname(realpath(__FILE__)) . '/christmas2015_init_page_expire.php');

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'show_homepage'
);
permanentlog($activity_log_file, json_encode($log_data));

// 检查用户是否两个都点亮了
$ignite_status = 0;
$ignite_love = $redis->get($rkey_imsi_ignite_love);
if ($ignite_love == $today) {
	$ignite_status |= 1;
}
$ignite_share = $redis->get($rkey_imsi_ignite_share);
if ($ignite_share == $today) {
	$ignite_status |= 2;
}

$tplObj->out['ignite_status'] = $ignite_status;

$tplObj->display('lottery/christmas2015/christmas2015_index.html');
exit;