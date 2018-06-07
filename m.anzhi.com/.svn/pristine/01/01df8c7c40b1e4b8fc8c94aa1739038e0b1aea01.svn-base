<?php
include_once ('./fun.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
session_begin();
$active_id = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];
$uid = $_SESSION['USER_UID'];
$imei = $_SESSION['DEVICEID'];

$log_data = array(
		'uid'=>$uid,
		'username' => $_SESSION['USER_NAME'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'time' => time(),
		'activity_id' => $active_id,
		'key' => 'share'
	);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
if(empty($uid)){
    echo 1;
}
else
{
	echo 2;
}
