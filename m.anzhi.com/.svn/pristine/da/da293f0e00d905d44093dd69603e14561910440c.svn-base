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
$imsi = $_SESSION['USER_IMSI'];
$imsi_share = "superman_lottery:share_{$imsi}_{$aid}";
$imsi_num = "superman_lottery:num_{$imsi}_{$aid}";
$imsi_share_msg = $redis -> gethash($imsi_share);
$today = date('Ymd',time());
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'share'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

if(!$imsi_share_msg[$today]){
	$redis -> sethash($imsi_share,array($today => 1));
	$now_num = $redis -> setx('incr',$imsi_num,1);
	$num_option = array(
		'where' => array(
			'imsi' => $imsi
		),
		'table' => 'superman_lottery_num'
	);
	$num_result = $model -> findOne($num_option,'lottery/lottery');
	if($num_result){
		$where = array(
			'imsi' => $imsi
		);
		$data = array(
			'lottery_num' => $now_num,
			'update_tm' => time(),
			'__user_table' => 'superman_lottery_num'
		);
		$result = $model -> update($where,$data,'lottery/lottery');
	}else{
		$data = array(
			'imsi' => $imsi,
			'lottery_num' => $now_num,
			'update_tm' => time(),
			'__user_table' => 'superman_lottery_num'
		);
		$result = $model -> insert($data,'lottery/lottery');
	}
	echo 300;
	return 300;
}else{
	echo 200;
	return 200;
}