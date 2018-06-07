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
if(!$imsi || $imsi == 000000000000000){
	$imsi_status = 1000;
	$tplObj -> out['imsi_status'] = $imsi_status;
}

if($_SESSION['VERSION_CODE'] < 5300){
	$tplObj -> out['channel_status'] = 1000;
}

//查询该用户是否中过将未填写信息
$have_option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'vacation_lottery_award'
);
$have_result = $model -> findOne($have_option,'lottery/lottery');
if($have_result){
	header("location:http://promotion.anzhi.com/lottery/vacation_lottery_info.php?sid={$_GET['sid']}");
}



//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'limit' => 10,
	'cache_time' => 600,
	'table' => 'vacation_lottery_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

if($all_award_result){
	$award_config_option = array(
		'where' => array(
			'config_type' => 'VACATION_LOTTERY_SECOND',
			'status' => 1
		),
		'cache_time' => 86401,
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

$tplObj -> out['all_award_result'] = $all_award_result;
$tplObj -> out['all_award_count'] = count($all_award_result);

//今日获取次数
$imsi_today_num = "vacation_lottery_second:today_num_{$imsi}_{$active_id}";
$today_num = $redis -> setx('incr',$imsi_today_num,0);
//今日剩余次数
$imsi_surplus = 3 - $today_num;
//获得抽奖次数
$imsi_num = "vacation_lottery_second:num_{$imsi}_{$active_id}";

$imsi_time = $redis -> gethash("vacation_lottery_second:time_{$imsi}_{$active_id}");
$imsi_package = "vacation_lottery_second:package_{$imsi}_{$active_id}";
$now = strtotime(date('Ymd 00:00:00'));
$imsi_num = "vacation_lottery_second:num_{$imsi}_{$active_id}";

if($imsi){
	if($imsi_time){
		if($imsi_time[0] < $now){
			$redis -> delete($imsi_num);
			$redis -> delete($imsi_today_num);
			$redis -> setx('incr',$imsi_num,1);
			$redis -> setx('incr',$imsi_today_num,0);
		}
	}else{
		//第一次进入 写库
		$new_data = array(
			'imsi' => $imsi,
			'lottery_num' => 1,
			'time' => time(),
			'__user_table' => 'vacation_lottery_num'
		);
		$insert_data = $model -> insert($new_data,'lottery/lottery');
		$redis -> setx('incr',$imsi_num,1);
		$redis -> setx('incr',$imsi_today_num,0);
	}
}
$imsi_nums = $redis -> setx('incr',$imsi_num,0);
$time = array(time());
$redis -> sethash("vacation_lottery_second:time_{$imsi}_{$active_id}",$time);
$tplObj -> out['imsi_surplus'] = $imsi_surplus;
$tplObj -> out['imsi_num'] = $imsi_nums;
$tplObj -> out['surplus_get_num'] = $imsi_surplus_get;
$tplObj -> out['img_url'] = getImageHost();
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display('vacation_lottery.html');