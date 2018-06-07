<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 186;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_POST['sid'] && eregi('[0-9a-zA-z]', $_POST['sid']) && strlen($_POST['sid']) == 32){
	session_id($_POST['sid']);
}
session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$package = $_POST['pkgname'];
$imsi_package = "vacation_lottery_second:package_{$imsi}_{$active_id}";
//今日获取次数
$imsi_today_num = "vacation_lottery_second:today_num_{$imsi}_{$active_id}";
//获得抽奖次数
$imsi_num = "vacation_lottery_second:num_{$imsi}_{$active_id}";
$my_package = $redis -> gethash($imsi_package);
$today_num = $redis -> setx('incr',$imsi_today_num,0);
$add_num = 0;
if(!in_array($package,$my_package) && $imsi && $imsi != '000000000000000'){
	$my_package[count($my_package)] = $package;
	if($today_num < 3){
		$redis -> sethash($imsi_package,$my_package);
		$today_num = $redis -> setx('incr',$imsi_today_num,1);
		$now_num = $redis -> setx('incr',$imsi_num,1);
		$add_num = 1;
	}else{
		$now_num = $redis -> setx('incr',$imsi_num,0);
	}
	$imsi_surplus = 3 - $today_num;
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_POST['sid'],
		'time' => time(),
		'num' => 1,
		'package' => $package,
		'key' => 'download_soft'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
}else{
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_POST['sid'],
		'time' => time(),
		'num' => 0,
		'package' => $package,
		'key' => 'download_soft'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$now_num = $redis -> setx('incr',$imsi_num,0);
}
$soft_list = $redis -> gethash("vacation_lottery_second:soft_{$active_id}");
$soft_name = $soft_list[$package];
$return_need = array($now_num,$soft_name,$imsi_surplus,$add_num);
echo json_encode($return_need);
return json_encode($return_need);


