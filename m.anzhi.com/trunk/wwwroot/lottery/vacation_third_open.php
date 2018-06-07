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
$softid = $_POST['softid'];
$package = $_POST['package'];
$imsi = $_SESSION['USER_IMSI'];
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