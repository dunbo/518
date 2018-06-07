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
$imsi = $_SESSION['USER_IMSI'];
$sid = $_GET['sid'];
$aid = $_GET['aid'];

$key=get_key();
if($key)
{
	$info=$redis -> get($key);
	$info_arr=json_decode($info, true);
	$info_arr['get_info']=1;
	$save_info=$redis -> set($key,json_encode($info_arr));
}

$imsi_info = "superman_lottery:info_{$imsi}_{$aid}";
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'superman_lottery_award'
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
			'status' => 0
		);
		$data = array(
			'telephone' => $telephone,
			'name' => $name,
			'status' => 1,
			'time' => time(),
			'__user_table' => 'superman_lottery_award'
		);
		$update_result = $model -> update($where,$data,'lottery/lottery');
	
		if($update_result){
			$log_data = array(
				'imsi' => $imsi,
				'imei' => $_SESSION['USER_IMEI'],
				'device_id' => $_SESSION['DEVICEID'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
				'award' => $result['award_level'],
				'telephone' => $telephone,
				'name' => $name,
				'time' => time(),
				'key' => 'award'
			);
			permanentlog('activity_'.$aid.'.log', json_encode($log_data));
			$redis -> delete($imsi_info);
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




