<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 187;
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
$name = $_GET['name'];
$telphone = $_GET['telphone'];
$address = $_GET['address'];
$award = $_GET['award'];
$user = $_GET['user'];
if(!preg_match("/^1[34578][0-9]{9}$/",$telphone) || strlen($telphone) != 11){
	echo 500;
	return 500;
	exit;
}

$where = array(
	'imsi' => $imsi,
	'award_level' => $award,
	'status' => 0
);

$data = array(
	'name' => $name,
	'telphone' => $telphone,
	'address' => $address,
	'time' => time(),
	'status' => 1,
	'__user_table' => 'vacation_third_award'
);

$result = $model -> update($where,$data,'lottery/lottery');

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'award_level' => $award,
	'user' => $user,
	'name' => $name,
	'telphone' => $telphone,
	'address' => $address,
	'time' => time(),
	'key' => 'award'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

if($result){
	echo 200;
	return 200;
}else{
	echo 300;
	return 300;
}







