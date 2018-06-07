<?php
//include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once ('./fun.php');
/*
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
//$aid = $active_id = $_POST['aid'];

session_begin($_POST['sid']);
*/

if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$package = $_POST['pkgname'];

//$uid = $_SESSION['USER_UID'];

//开始增加次数的任务
if(!empty($uid)){//没有登录不记
    $redis->sethash($p_key,array($package => 1));
    $redis->expire($p_key,86400*90);
}


$real_package = $redis->get("real_package:post_old_package_{$package}:");
if(!$real_package)
{
	$real_package = get_real_package($package);
}
$telphone = $_POST['telphone'];
$az_user = $_POST['az_user'];
$server_name = $_POST['server_name'];
$play_name = $_POST['play_name'];
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
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
brush_second_do($aid);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
