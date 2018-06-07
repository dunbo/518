<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = $_POST['aid'];
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin($_POST['sid']);
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$package = $_POST['package'];

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_POST['sid'],
	'time' => time(),
	'package' => $package,
	'key' => 'download_soft'
);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
