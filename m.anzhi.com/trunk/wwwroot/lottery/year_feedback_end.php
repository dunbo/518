<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 183;
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
if(!$imsi || $imsi == 000000000000000){
	$imsi_status = 1000;
	$tplObj -> out['imsi_status'] = $imsi_status;
}

if($_SESSION['VERSION_CODE'] < 5300){
	$tplObj -> out['channel_status'] = 1000;
}

$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display('year_feedback_end.html');
