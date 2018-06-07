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
$aid = $_GET['aid'];
$name = $_GET['name'];
$telephone = $_GET['telephone'];
$imsi = $_SESSION['USER_IMSI'];
$imsi_info = "friends_lottery:info_{$imsi}_{$aid}";

$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'friends_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');

if($result){
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
			'update_tm' => time(),
			'__user_table' => 'friends_lottery_award'
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
