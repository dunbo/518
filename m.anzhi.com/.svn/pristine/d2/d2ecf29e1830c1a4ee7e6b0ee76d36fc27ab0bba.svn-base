<?php

require_once(dirname(realpath(__FILE__)) . '/init.php');

$actsid = get_key("springfestival2016:{$aid}");
$share_day = $redis->gethash($actsid, 'share_day');
if ($share_day != $today) {
	$share_day = $today;
	// 设置用户的跳转url
	if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
		$jump_url_host = 'http://118.26.203.23';
	} else {
		$jump_url_host = 'http://promotion.anzhi.com';
	}
	$jump_url = $jump_url_host . '/lottery/springfestival2016/index.php';
	$arr = array(
		'share_day' => $share_day,
		'url' => $jump_url,
	);
	$redis->sethash($actsid, $arr, $r_cache_time);
}

$log_data = array(
	'actsid' => $actsid,//端外标识id
    'imsi' => $imsi,//端外无法获得此值，此值为空
    'device_id' => $_SESSION['DEVICEID'],//端外无法获得此值，此值为空
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],//端外无法获得此值，此值为空
    'time' => time(),
	"users" => '',
    "uid" => '',
    'key' => 'promotion_share_soft'
);
permanentlog($activity_log_file, json_encode($log_data));

$ret = array(
	'status' => 200,
	'ever_shared' => 1
);

exit(json_encode($ret));