<?php

include_once (dirname(realpath(__FILE__)).'/init_page_expire.php');

// 今天是否分享过
$share_day = $redis->get($rkey_imsi_share);
$ever_shared = 0;
if ($share_day == $today) {
	$ever_shared = 1;
}
$tplObj->out['ever_shared'] = $ever_shared;

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'actsid' => $_GET['actsid'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'index'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display('lottery/guessappbattle/index.html');