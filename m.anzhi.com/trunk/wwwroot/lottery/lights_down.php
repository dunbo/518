<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}
$active_id = 200;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

session_start();

if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$share_num_1 = $redis -> setx('incr',"lights_{$active_id}:share_1",0);
$share_num_2 = $redis -> setx('incr',"lights_{$active_id}:share_2",0);
$share_num_3 = $redis -> setx('incr',"lights_{$active_id}:share_3",0);
$share_num_4 = $redis -> setx('incr',"lights_{$active_id}:share_4",0);
$share_num_5 = $redis -> setx('incr',"lights_{$active_id}:share_5",0);
$share_num_all = $share_num_1 + $share_num_2 + $share_num_3 + $share_num_4 + $share_num_5;

$tplObj -> out['share_num_all'] = $share_num_all;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display("lights_down.html");
