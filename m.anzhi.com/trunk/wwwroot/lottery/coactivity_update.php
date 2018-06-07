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
if(ctype_digit($aid)==false){
    exit(0);
}
$imsi = $_SESSION['USER_IMSI'];
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
	'uid' => '',
    'key' => 'update_focus'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));
echo 200;
return 200;
