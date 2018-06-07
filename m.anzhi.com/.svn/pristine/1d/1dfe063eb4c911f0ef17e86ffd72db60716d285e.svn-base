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
$imei = $_SESSION['USER_IMEI'];
$imsi = $_SESSION['USER_IMSI'];
$today = date('Ymd');
$imei_num = "set_like:{$today}_{$imei}_{$aid}";
$all_num = "set_like:all_num_{$aid}";
$now_num = $redis -> setx('incr',$imei_num,0);
if($now_num > 0 && $now_num ){
	$now_num = $redis -> setx('incr',$imei_num,-1);
	$where = array(
		'aid' => $aid
	);
	$data = array(
		'num' => array('exp','num + 1'),
		'update' => time(),
		'__user_table' => 'setlike_num'
	);
	$result = $model -> update($where,$data,'lottery/lottery');
	$redis -> setx('incr',$all_num,1);
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => 'get_like'
	);
	permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	echo 200;
	return 200;
}else{
	echo 300;
	return 300;
}