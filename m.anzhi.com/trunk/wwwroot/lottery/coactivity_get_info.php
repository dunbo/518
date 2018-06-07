<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$name = $_GET['name'];
$telphone = $_GET['telphone'];
$address = $_GET['address'];
session_begin();
$imsi = $_SESSION['USER_IMSI'];
$imsi_info = "general_lottery_imsi:info_{$aid}";
if(!preg_match("/^1[34578][0-9]{9}$/",$telphone) || strlen($telphone) != 11){
	echo 500;
	return 500;
	exit;
}
$option = array(
	'where' => array(
		'aid' => $aid,
		'imsi' => $imsi,
		'status' => 0
	),
	'cache_time' => 60,
	'table' => 'gm_lottery_award'
);
$my_result = $model -> findOne($option,'lottery/lottery');
$pid_option = array(
	'where' => array(
		'pid' => $my_result['pid'],
	),
	'cache_time' => 300,
	'table' => 'gm_lottery_prize'
);
$pid_result = $model -> findOne($pid_option,'lottery/lottery');
$aid_option = array(
	'where' => array(
		'id' => $my_result['aid']
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$aid_result = $model -> findOne($aid_option);
$page_option = array(
	'where' => array(
		'ap_id' => $aid_result['activity_page_id']
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);

$where = array(
	'aid' => $aid,
	'imsi' => $imsi,
	'status' => 0
);

$data = array(
	'status' => 1,
	'name' => $name,
	'telphone' => $telphone,
	'address' => $address,
	'time' => time(),
	'__user_table' => 'gm_lottery_award'
);
$result = $model -> update($where,$data,'lottery/lottery');

if($result){
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'award_level' => $pid_result['level'],
		'pid' => $my_result['pid'],
		'user' => '',
		'uid' => '',
		'name' => $name,
		'tel' => $telphone,
		'address' => $address,
		'package' => '',
		'gift' => '',
		'users' => '',
		'uid' => '',
		'lottery_type' => $page_result['lottery_style'],
		'award_name' => $pid_result['name'],
		'award_id' => $my_result['id'],
		'key' => 'award'
	);
	permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	$all_info = $redis -> gethash($imsi_info,$imsi);
	unset($all_info);
	$redis -> sethash($imsi_info,array($imsi => $all_info));
	echo 200;
	return 200;
	exit;
}else{
	echo 400;
	return 400;
}

