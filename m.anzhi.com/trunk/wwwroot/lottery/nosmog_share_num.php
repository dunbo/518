<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 199;
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

$share_id = $_GET['share_id'];

$share_num = "nosmog_{$active_id}:share_{$share_id}";
$new_num = $redis -> setx('incr',$share_num,0);
if($new_num){
	$new_num = $redis -> setx('incr',$share_num,1);
}else{
	if($share_id == 1){
		$start_num = 54371;
	}else if($share_id == 2){
		$start_num = 36001;
	}else if($share_id == 3){
		$start_num = 12987;
	}else if($share_id == 4){
		$start_num = 9854;
	}
	$new_num = $redis -> setx('incr',$share_num,$start_num);
}

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'share_id' => $share_id,
	'new_num' => $new_num,
	'key' => 'install_soft'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

echo $new_num;
return $new_num;
















