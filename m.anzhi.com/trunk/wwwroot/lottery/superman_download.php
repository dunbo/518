<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];
$package = $_GET['pkgname'];
$imsi_num = "superman_lottery:num_{$imsi}_{$aid}";
$imsi_package = "superman_lottery:package_{$imsi}_{$aid}";
$my_package = $redis -> gethash($imsi_package);

if(!$my_package[$package]){
	$log_data = array(
		'imsi' => $imsi,
		'imei' => $_SESSION['USER_IMEI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'num' => 1,
		'package' => $package,
		'key' => 'download'
	);
	permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	$now_num = $redis -> setx('incr',$imsi_num,1);
	$redis -> sethash($imsi_package,array($package => 1));
	$where = array(
		'imsi' => $imsi
	);
	$data = array(
		'lottery_num' => $now_num,
		'update_tm' => time(),
		'__user_table' => 'superman_lottery_num'
	); 
	$result = $model -> update($where,$data,'lottery/lottery');
	echo $now_num;
	return $now_num;
}else{
	$log_data = array(
		'imsi' => $imsi,
		'imei' => $_SESSION['USER_IMEI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'num' => 0,
		'package' => $package,
		'key' => 'download'
	);
	permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	echo 200;
	return 200;
}

