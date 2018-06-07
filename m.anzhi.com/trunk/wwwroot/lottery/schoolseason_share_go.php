<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$sid = $_GET['sid'];
$aid = $_GET['aid'];
session_begin();
$model = new GoModel();
$imsi = $_SESSION['USER_IMSI'];
$imsi_share = "schoolseason_lottery:share_{$imsi}_{$aid}";
$imsi_num = "schoolseason_lottery:num_{$imsi}_{$aid}";
$time = date('Ymd');
$my_share = $redis -> gethash($imsi_share,$time);
if(!$my_share){
	$redis -> sethash($imsi_share,array($time => 1));
	$now_num = $redis -> setx('incr',$imsi_num,0);
	if($now_num){
		$redis -> setx('incr',$imsi_num,1);
		$where = array(
			'imsi' => $imsi
		);
		$data = array(
			'num' => $now_num,
			'__user_table' => 'schoolseason_lottery_num'
		);
		$model -> update($where,$data,'lottery/lottery');
	}else{
		$now_num = $redis -> setx('incr',$imsi_num,1);
		$data = array(
			'imsi' => $imsi,
			'num' => $now_num,
			'create_tm' => time(),
			'status' => 1,
			'__user_table' => 'schoolseason_lottery_num'
		);
		$result = $model -> insert($data,'lottery/lottery');
	}
}
$log_data = array(
	'activity_id' => $aid,
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'share_soft'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));
echo 200;
return 200;