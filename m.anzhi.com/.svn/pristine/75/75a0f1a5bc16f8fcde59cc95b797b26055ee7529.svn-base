<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 195;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_POST['sid'] && eregi('[0-9a-zA-z]', $_POST['sid']) && strlen($_POST['sid']) == 32){
	session_id($_POST['sid']);
}
session_start();
$imsi = $_SESSION['USER_IMSI'];
$softid = $_POST['softid'];
$package = $_POST['package'];

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'package' => $package,
	'sid' => $_POST['sid'],
	'time' => time(),
	'key' => 'open_soft'
);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
return 200;