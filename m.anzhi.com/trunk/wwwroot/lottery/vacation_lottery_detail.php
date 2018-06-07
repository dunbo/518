<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 186;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}

session_start();
$imsi = $_SESSION['USER_IMSI'];

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'key' => 'detail'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

$tplObj -> display('vacation_lottery_detail.html');