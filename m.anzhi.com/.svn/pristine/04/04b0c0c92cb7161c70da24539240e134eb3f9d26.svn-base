<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 179;
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

/* $aa = array(1 => array('一等奖','小米3移动版16G手机一台'),2=>array('二等奖','索尼Walkman一体式音乐播放器一个'),3=>array('三等奖','小米移动电源一个'),4=>array('四等奖','50元充值卡一张'),5=>array('五等奖','10元充值卡一张'),6=>array('六等奖','5元充值卡一张'));
echo json_encode($aa);exit; */


$award_info_arr = $redis -> gethash("award_{$imsi}:lottery_{$active_id}");
if(!$award_info_arr){
	$award_option = array(
		'where' => array(
			'imsi' => $imsi
		),
		'order' => 'time DESC',
		'cache_time' => 86400,
		'table' => 'christmas_award'
	);
	$award_result = $model -> findAll($award_option,'lottery/lottery');
	foreach($award_result as $key => $val){
		if($val['award'] <= 3){
			$v['award'] = $val['award'];
			$v['telphone'] = $v['telphone'];
			$v['name'] = $v['name'];
			$v['address'] = $v['address'];
			$v['status'] = $val['status'];
			$v['time'] = $val['time'];
		}elseif($val['award'] >= 4 && $val['award'] <= 6){
			$v['award'] = $val['award'];
			$v['telphone'] = $v['telphone'];
			$v['status'] = $val['status'];
			$v['time'] = $val['time'];
		}
		$award_info_arr[$key] = $v;
	}
	$redis -> sethash("award_{$imsi}:lottery_{$active_id}",$award_info_arr);
}

foreach($award_info_arr as $key => $val){
	if($val['status'] == 1){
		$v['times'] = date('Y-m-d  H:i',$val['time']);
		$v['time'] = $val['time'];
		$v['telphone'] = $val['telphone'];
		$v['name'] = $val['name'];
		$v['address'] = $val['address'];
		$level_option = array(
			'where' => array(
				'config_type' => 'CHRISTMAS_AWARD',
				'status' => 1
			),
			'cache_time' => 86400,
			'table' => 'pu_config'
		);
		$level_result = $model -> findOne($level_option);
		$level_info = json_decode($level_result['configcontent'],true);
		$v['award_level'] = $level_info[$val['award']-1][0];
		$v['award_price'] = $level_info[$val['award']-1][1];
		$award_info[] = $v;
	}
}
foreach($award_info as $key => $val){
	$the_time[] = $val['time'];
}
array_multisort($award_info,SORT_DESC,$the_time);


$tplObj -> out['award_info'] = $award_info;
$tplObj -> display('christmas_award_info.html');