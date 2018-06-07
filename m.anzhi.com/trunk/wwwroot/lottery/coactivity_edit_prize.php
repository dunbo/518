<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$id = $_GET['id'];
$imsi = $_SESSION['USER_IMSI'];
$name = $_GET['name'];
$telephone = $_GET['telephone'];
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 1,
		'id' => $id
	),
	'table' => 'gm_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');

if($result){
	$telephone = $_GET['telephone'];
	$name = $_GET['name'];
	if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11){
		echo 500;
		return 500;
	}else{
		$where = array(
			'imsi' => $imsi,
			'id' => $id
		);
		$data = array(
			'telephone' => $telephone,
			'name' => $name,
			'time' => time(),
			'__user_table' => 'gm_lottery_award'
		);
		$update_result = $model -> update($where,$data,'lottery/lottery');
	
		if($update_result){
			$log_data = array(
				'activity_id' => $aid,
				'imsi' => $imsi,
				'device_id' => $_SESSION['DEVICEID'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
				'tel' => $telephone,
				'name' => $name,
				'time' => time(),
				'users' => '',
				'uid' => '',
				'key' => 'info_edit'
			);
			permanentlog('activity_'.$aid.'.log', json_encode($log_data));
			echo 200;
			return 200;
		}else{
			echo 300;
			return 300;
		}
	}
}else{
	echo 300;
	return 300;
}
