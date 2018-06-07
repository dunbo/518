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

$telphone = $_POST['telphone'];
$az_user = $_POST['az_user'];
$server_name = $_POST['server_name'];
$play_name = $_POST['play_name'];


$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'chl_cid' => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'package' => $package,
	'sid' => $_POST['sid'],
	'time' => time(),
	'key' => 'install_soft'
);

if(!empty($telphone)){
    $log_data['telphone'] =$telphone;
}

if(!empty($az_user)){
    $log_data['az_user'] =$az_user;
    $log_data['server_name'] =$server_name;
    $log_data['play_name'] =$play_name;
}


permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
return 200;
