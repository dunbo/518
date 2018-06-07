<?php

/*
** 活动首页
*/

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');
include_once (dirname(realpath(__FILE__)).'/aprilstrip_init_page.php');

// 是否手动访问此网页，手动访问此页面（非第一次访问）不跳转到魔镜页
$manual = empty($_GET['manual']) ? 0 : 1;

// 从缓存判断该用户是否访问过首页
$already_visit_index = $redis->get($rkey_imsi_already_visit_index);
if (!$manual && $already_visit_index) {
	// 跳转到魔镜页
	header("location:/lottery/aprilstrip_mirror.php?sid={$_GET['sid']}");
	exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'aprilstrip_index'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display("aprilstrip_index.html");
exit;

