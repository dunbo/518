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
$key_actsid=get_key();
if($key_actsid)
{
	$info= $redis -> get($key_actsid);
	$info['get_lottery']=1;
	$info['url']="http://118.26.203.23/lottery/christmas_rules.php";
	$save_info=$redis -> set($key_actsid,$info);
}
session_start();
$imsi = $_SESSION['USER_IMSI'];
$imsi_redis = $imsi.":lottery_{$active_id}";
$imsi_num = $imsi.":lottery_num_{$active_id}";
$imsi_info = $imsi.":info_{$active_id}";

//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'limit' => 10,
	'cache_time' => 600,
	'table' => 'christmas_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

if($all_award_result){
	$award_config_option = array(
		'where' => array(
			'config_type' => 'CHRISTMAS_AWARD',
			'status' => 1
		),
		'cache_time' => 86400,
		'table' => 'pu_config'
	);
	$award_config_result = $model -> findOne($award_config_option);
	$award_level = json_decode($award_config_result['configcontent'],true);
	foreach($all_award_result as $key => $val){
		$val['award'] = $award_level[$val['award']-1][1].'一张';
		$val['the_time'] = date('Y-m-d',$val['time']);
		$val['telphone'] = substr_replace($val['telphone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
}

$tplObj -> out['all_award_result'] = $all_award_result;
$tplObj -> out['all_award_count'] = count($all_award_result);

if(!$imsi || $imsi == '000000000000000'){
	$tplObj -> out['status'] = 100;
}else{
	//查询该imsi是否有中过奖
	$my_info = $redis -> gethash($imsi_info);

	if($my_info){
		$award_result = $my_info[1];
		$result = $my_info[0];
	}else{
		$award_option = array(
			'where' => array(
				'imsi' => $imsi,
				'status' => 0
			),
			'table' => 'christmas_award'
		);
		$award_results = $model -> findOne($award_option,'lottery/lottery');
		
		$option = array(
			'where' => array(
				'imsi' => $imsi,
			),
			'table' => 'christmas_lottery'
		);
		$results = $model -> findOne($option,'lottery/lottery');
		if(!$award_results){
			$award_result_go = 0;
		}
		$the_info = array($results['time'],$award_result_go);
		$redis -> sethash($imsi_info,$the_info);
		$award_result = $award_results['award'];
		$result = $results['time'];
	}
	if($award_result){
		$award_level_option = array(
			'where' => array(
				'config_type' => 'CHRISTMAS_AWARD',
				'status' => 1
			),
			'cache_time' => 86400,
			'table' => 'pu_config'
		);
		$award_level_result = $model -> findOne($award_level_option);
		$award_level_arr = json_decode($award_level_result['configcontent'],true);
		$award_level = $award_level_arr[$award_result - 1][0];
		$prize = $award_level_arr[$award_result - 1][1];
		$tplObj -> out['award_level'] = $award_level;
		$tplObj -> out['prize'] = $prize;
		if($award_result <= 6){
			$tplObj -> out['status'] = 2000;
		}
	}else{
		$tplObj -> out['status'] = 200;
	}
	$my_num = $redis -> setx('incr',$imsi_num,0);
	if(!$my_num){
		$num_option = array(
			'where' => array(
				'imsi' => $imsi,
			),
			'table' => 'christmas_lottery'
		);
		$num_result = $model -> findOne($num_option,'lottery/lottery');
		$my_num = $num_result['lottery_num'];
		$my_num = $redis -> setx('incr',$imsi_num,$my_num);
	}
	$tplObj -> out['my_num'] = $my_num;
}

//$redis -> setx('incr',$imsi_num,10);
$one_time = strtotime(date('20141223 00:00:00'));
$two_time = strtotime(date('20141225 00:00:00'));
$three_time = strtotime(date('20141225 23:59:59'));
$now_time = time();
$tplObj -> out['one_time'] = $one_time;
$tplObj -> out['now_time'] = $now_time;
$tplObj -> out['two_time'] = $two_time;
$tplObj -> out['three_time'] = $three_time;
$tplObj -> out['img_url'] = "http://apk.goapk.com/";
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['actsid'] = $key_actsid;
$tplObj -> out['aid'] = $active_id;
$tplObj -> display('christmas_lottery.html');