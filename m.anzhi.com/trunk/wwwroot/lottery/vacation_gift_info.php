<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 185;
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
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$imsi_gift_redis = "vacation_lottery:{$imsi}_gift_{$active_id}";
$my_gift = $redis -> gethash($imsi_gift_redis);
//rsort($my_gift);
$soft_info = $redis -> gethash("vacation_lottery:soft_{$active_id}");
foreach($my_gift as $key => $val){
	$the_time[] = $val['time'];
}
ksort($the_time);
array_multisort($the_time, SORT_DESC, $my_gift); 
foreach($my_gift as $key => $val){
	$val['soft_name'] = $soft_info[$val['package']];
	$val['the_time'] = date('Y-m-d H:i',$val['time']);
	$my_gift[$key] = $val;
}

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'key' => 'gift_info'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

$tplObj -> out['my_gift'] = $my_gift;
$tplObj -> display('vacation_gift_info.html');

