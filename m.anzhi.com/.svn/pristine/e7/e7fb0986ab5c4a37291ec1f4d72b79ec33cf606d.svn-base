<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 179;
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

$imsi = $_SESSION['USER_IMSI'];
$imsi_num = $imsi.":lottery_num_{$active_id}";
$imsi_share = $imsi.":share_time_{$active_id}";
if($imsi){
$before_share_time = $redis -> gethash($imsi_share);
$today_time = strtotime(date('Ymd 00:00:00'));
if(!$before_share_time || $before_share_time['time'] < $today_time){
	$new_num = $redis -> setx('incr',$imsi_num,1);
	$num_where = array(
		'where' => array(
			'imsi' => $imsi
		),
		'table' => 'christmas_lottery'
	);
	$num_data = array(
		'lottery_num' => $new_num,
		'time' => time(),
		'__user_table' => 'school_lottery'
	);
	$num_result = $model -> update($num_where,$num_data,'lottery/lottery');
	if(!$num_result){
		$num_data = array(
			'lottery_num' => $new_num,
			'time' => time(),
			'imsi' => $imsi,
			'__user_table' => 'christmas_lottery'
		);
		$insert_data = $model -> insert($num_data,'lottery/lottery');
	}
}

$time = array('time' => time());
$redis -> sethash($imsi_share,$time);
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'key' => 'christmas_lottery_share'
);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

echo 200;
return 200;
}


