<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$feature_id = $_POST['aid'];
session_begin($_POST['sid']);
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$package = $_POST['pkgname'];
$type = $_GET['type'];
if($type=="download")
{
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'feature_id' => $feature_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_POST['sid'],
		'time' => time(),
		'package' => $package,
		'key' => 'download_soft'
	);
	permanentlog('new_feature_'.$feature_id.'.log', json_encode($log_data));
	return 200;
}
elseif($type=="install")
{
	$softid = $_POST['softid'];
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'feature_id' => $feature_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'package' => $package,
		'sid' => $_POST['sid'],
		'time' => time(),
		'key' => 'install_soft'
	);
	permanentlog('new_feature_'.$feature_id.'.log', json_encode($log_data));
	return 200;
}
elseif($type=="open")
{
	$softid = $_POST['softid'];
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'feature_id' => $feature_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'package' => $package,
		'sid' => $_POST['sid'],
		'time' => time(),
		'key' => 'open_soft'
	);
	permanentlog('new_feature_'.$feature_id.'.log', json_encode($log_data));
	return 200;
}
elseif($type=="share")
{
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'feature_id' => $feature_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_POST['sid'],
		'time' => time(),
		'key' => 'share'
	);
	permanentlog('new_feature_'.$feature_id.'.log', json_encode($log_data));
	return 200;
}

