<?php

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init_page.php');

// 在缓存中记录此用户已访问过首页（因为此页面是从首页跳转过来的）
$redis->set($rkey_imsi_already_visit_index, 1, $r_cache_time);

// 默认拯救王子页面
$is_girl = 0;
if (!empty($_GET['is_girl'])) {
	// 拯救公主页面
	$is_girl = 1;
}

// 记日志
$log_data = array(
	'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
	'device_id' => $_SESSION['DEVICEID'],
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'is_girl' => $is_girl,
	'time' => time(),
	'key' => 'aprilstrip_strip'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->out['is_girl'] = $is_girl;
$tplObj->display("aprilstrip_strip.html");