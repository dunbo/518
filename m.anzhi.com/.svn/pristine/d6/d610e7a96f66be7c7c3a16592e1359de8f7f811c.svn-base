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
$id = $_GET['id'];
$telephone = $_GET['telephone'];
$name = $_GET['name'];
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 1,
		'id' => $id
	),
	'table' => 'schoolseason_lottery_award'
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
			'update_tm' => time(),
			'__user_table' => 'schoolseason_lottery_award'
		);
		$update_result = $model -> update($where,$data,'lottery/lottery');
	
		if($update_result){
			$log_data = array(
				'activity_id' => $aid,
				'imsi' => $imsi,
				'imei' => $_SESSION['USER_IMEI'],
				'device_id' => $_SESSION['DEVICEID'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
				'telephone' => $telephone,
				'name' => $name,
				'time' => time(),
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




