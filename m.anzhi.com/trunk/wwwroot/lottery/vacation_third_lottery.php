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

if(!$imsi || $imsi == 000000000000000){
	$imsi_status = 1000;
	$tplObj -> out['imsi_status'] = $imsi_status;
}

if($_SESSION['VERSION_CODE'] < 5300){
	$tplObj -> out['channel_status'] = 1000;
}

//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'limit' => 10,
	'cache_time' => 600,
	'table' => 'vacation_third_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

if($all_award_result){
	$award_config_option = array(
		'where' => array(
			'config_type' => 'VACATION_THIRD_AWARD',
			'status' => 1
		),
		'cache_time' => 86400,
		'table' => 'pu_config'
	);
	$award_config_result = $model -> findOne($award_config_option);
	$award_level = json_decode($award_config_result['configcontent'],true);
	foreach($all_award_result as $key => $val){
		$val['award'] = $award_level[$val['award_level']-1][1];
		$val['the_time'] = date('Y-m-d',$val['time']);
		$val['telphone'] = substr_replace($val['telphone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
}

//判断当前imsi是否有中奖未填写
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'vacation_third_award'
);
$result = $model -> findOne($option,'lottery/lottery');
if($result){
	header("location:http://promotion.anzhi.com/lottery/vacation_third_info.php?sid={$_GET['sid']}");
}


$tplObj -> out['all_award_result'] = $all_award_result;
$tplObj -> out['all_award_count'] = count($all_award_result);
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display("vacation_third_lottery.html");