<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$active_id = $_POST['aid'];
$model = new GoModel();
session_begin($_POST['sid']);

if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$package = $_POST['pkgname'];

$real_package = $redis->get("real_package:post_old_package_{$package}:");
if(!$real_package){
	$real_package = get_real_package($package);
}

//记录某一时刻下载的次数
brush_second_do($active_id);

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
	'sid' => $_POST['sid'],
	'time' => time(),
	'package' => $package,
	'real_package' => $real_package,
	'key' => 'download_soft'
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
