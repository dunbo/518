<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
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

$imsi = $_SESSION['USER_IMSI'];
$imsi_redis = $imsi.":lottery";
$imsi_num = $imsi.":lottery_num";


$softid = $_GET['softid'];
$soft_option = array(
	'where' => array(
		'softid' => $softid,
		'status' => 1,
		'hide' => 1
	),
	'field' => 'package',
	'table' => 'sj_soft'
);
$package_result = $model -> findOne($soft_option);
$package = $package_result['package'];
$the_package = $redis -> gethash($imsi_redis,$package);

if(!$the_package){
	$my_package_json = $redis -> gethash($imsi_redis);
	$my_package_arr = count($my_package_json);
	
	if($my_package_arr < 4){
		$add_num = 1;
	}elseif($my_package_arr == 4){
		$add_num = 2;
	}elseif($my_package_arr > 4 && $my_package_arr < 9){
		$add_num = 1;
	}elseif($my_package_arr == 9){
		$add_num = 4;
	}elseif($my_package_arr > 9 && $my_package_arr < 19){
		$add_num = 1;
	}elseif($my_package_arr == 19){
		$add_num = 6;
	}elseif($my_package_arr >= 20){
		$add_num = 2;
	}
	$redis -> sethash($imsi_redis,array($package => $add_num));
	$now_package_json = $redis -> gethash($imsi_redis);
	$now_package_arr = count($now_package_json);
	$new_num = $redis -> setx('incr',$imsi_num,$add_num);
	if($now_package_arr < 5){
		$download_more = 5 - $now_package_arr;
		$more = 1;
	}elseif($now_package_arr >= 5 && $now_package_arr < 10){
		$download_more = 10 - $now_package_arr;
		$more = 3;
	}elseif($now_package_arr >= 10 && $now_package_arr < 20){
		$download_more = 20 - $now_package_arr;
		$more = 5;
	}else{
		$download_more = 0;
		$more = 0;
	}
	
	$new_num_data = array(
		'lottery_num' => $new_num,
		'time' => time(),
		'__user_table' => 'nd_lottery'
	);
	
	$new_num_where = array(
		'imsi' => $imsi
	);
	$new_num_result = $model -> update($new_num_where,$new_num_data,'lottery/lottery');
	
	$json_arr = array($new_num,$now_package_arr,$download_more,$more);
	if($new_num_result){
		echo json_encode($json_arr);
		return json_encode($json_arr);
	}
}


