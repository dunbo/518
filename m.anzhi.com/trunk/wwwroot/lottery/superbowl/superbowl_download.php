<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once (dirname(realpath(__FILE__)).'/superbowl_fun.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
session_begin();
$aid = $_GET['aid'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

session_begin();

$activity_id = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];

$package = $_GET['package'];
$download_redis = "superbowl_{$aid}_download_app:{$imsi}";
$rkey_imsi_lottery_num = "superbowl_{$aid}_{$imsi}_lottery_num";//用户可抽奖次数
$log_data = array(
	'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $activity_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'num' => 1,
	'package' => $package,
	'key' => 'download_soft'
);

permanentlog('activity_'.$activity_id.'.log', json_encode($log_data));
$model = new GoModel();
//$redis->delete($download_redis);
$has_download = $redis->gethash($download_redis,$package);
if($has_download!=1){
	//设置可抽奖数加1
	$now_num = $redis -> setx('incr',$rkey_imsi_lottery_num,1);
	setLotteryNum($now_num);
	$redis -> sethash("{$download_redis}",array("{$package}"=>1),86400*30);
	echo $now_num;
	return $now_num;
}else{

	echo 200;
	return 200;
}



