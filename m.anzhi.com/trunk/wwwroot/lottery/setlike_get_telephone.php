<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$aid = $_GET['aid'];
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$telephone = $_GET['telephone'];
$imei = $_SESSION['USER_IMEI'];
if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11){
	echo 500;
	exit;
}
$imsi = $_SESSION['USER_IMSI'];
$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 3600,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);
$pid =  $activity_result['activity_page_id'];
$page_option = array(
	'where' => array(
		'ap_id' => $pid
	),
	'cache_time' => 3600,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);
$the_telephone = "set_like:telephone_{$aid}";
$all_telephone = $redis -> gethash($the_telephone);
if(!$all_telephone[$imei]){
	$all_telephone[$imei] = 1;
	$redis -> sethash($the_telephone,$all_telephone);
}else{
	echo 200;
	return 200;
}
if($page_result['end_tm'] > time()){
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'telephone' => $telephone,
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => 'get_telephone'
	);

	permanentlog('activity_'.$aid.'.log', json_encode($log_data));
}
echo 200;
return 200;