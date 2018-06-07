<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$aid = $_GET['aid'];
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$softid = $_GET['softid'];
$package = $_GET['package'];
$imsi = $_SESSION['USER_IMSI'];
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'package' => $package,
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'open_soft'
);

permanentlog('activity_'.$aid.'.log', json_encode($log_data));
return 200;