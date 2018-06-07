<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 186;
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

$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'vacation_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');

$end_time = strtotime('20150211 24:00:00');
if($end_time < time()){
	$gos = 1;
}else{
	$gos = 2;
}

$tplObj -> out['gos'] = $gos;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['award'] = $result['award_level'];
$tplObj -> display("vacation_lottery_info.html");


