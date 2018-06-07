<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 187;
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
$user = $_GET['user'];

$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'vacation_third_award'
);
$result = $model -> findOne($option,'lottery/lottery');

$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['user'] = $_GET['user'];
$tplObj -> out['award'] = $result['award_level'];
$tplObj -> display("vacation_third_info.html");