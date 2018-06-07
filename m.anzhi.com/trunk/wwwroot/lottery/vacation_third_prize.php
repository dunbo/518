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

$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => array('exp'," = 1 or status = 2")
	),
	'order' => 'time desc',
	'table' => 'vacation_third_award'
);
$result = $model -> findAll($option,'lottery/lottery');

$content_option = array(
	'where' => array(
		'config_type' => 'VACATION_THIRD_AWARD',
		'status' => 1
	),
	'table' => 'pu_config'
);

$content_result = $model -> findOne($content_option);
$award_content = json_decode($content_result['configcontent'],true);
$soft_name_arr = $redis -> gethash("vacation_lottery_second:soft_{$active_id}");
foreach($result as $key => $val){
	if($val['status'] == 2){
		$val['soft_name'] = $soft_name_arr[$val['package']];
	}else if($val['status'] == 1){
		$val['prize'] = $award_content[$val['award_level'] - 1][1];
	}
	$val['the_time'] = date('Y-m-d',$val['time']);
	$result[$key] = $val;
}

$tplObj -> out['result'] = $result;
$tplObj -> display("vacation_third_prize.html");