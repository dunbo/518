<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = $_GET['aid'];

$key=get_key();
if($key)
{
	$info=$redis -> get($key);
	$info_arr=json_decode($info, true);
	$info_arr['get_info']=1;
	$save_info=$redis -> set($key,json_encode($info_arr));
}

$imsi = $_SESSION['USER_IMSI'];
$imsi_num = "superman_lottery:num_{$imsi}_{$aid}";
$imsi_info = "superman_lottery:info_{$imsi}_{$aid}";

if(!$imsi){
	header("location:/lottery/superman_share.php?sid={$sid}&aid={$aid}");
}

//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1,
		'award_level' => array('exp',' <= 3'),
	),
	'order' => 'time desc',
	'limit' => 10,
	'table' => 'superman_lottery_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

$award_info_option = array(
	'where' => array(
		'config_type' => 'SUPERMAN_AWARD',
		'status' => 1
	),
	'cache_time' => 86402,
	'table' => 'pu_config'
);
$award_info_result = $model -> findOne($award_info_option);
$award_content = json_decode($award_info_result['configcontent'],true);
if($all_award_result){
	foreach($all_award_result as $key => $val){
		$val['award'] = $award_content[$val['award_level']][1];
		$val['the_time'] = date('Y-m-d',$val['time']);
		$val['telephone'] = substr_replace($val['telephone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
	$tplObj -> out['all_award_result'] = $all_award_result;
	$tplObj -> out['all_award_count'] = count($all_award_result);
}

$my_info = $redis -> gethash($imsi_info);
//是否有中奖未填写信息
if($my_info){
	$award_level = $award_content[$my_info[1]][0];
	$award_prize = $award_content[$my_info[1]][1];
	$tplObj -> out['lottery_status'] = 200;
	$tplObj -> out['award_level'] = $award_level;
	$tplObj -> out['award_prize'] = $award_prize;
}else{
	$award_option = array(
		'where' => array(
			'imsi' => $imsi,
			'status' => 0,
			'award_level' => array('exp','<=2')
		),
		'table' => 'superman_lottery_award'
	);
	$award_result = $model -> findOne($award_option,'lottery/lottery');
	if($award_result){
		$my_award = array($award_result['imsi'],$award_result['award_level'],$award_result['time'],$award_status['status']);
		$redis -> sethash($imsi_info,$my_award);
		$award_level = $award_content[$my_award[1]][0];
		$award_prize = $award_content[$my_award[1]][1];
		$tplObj -> out['award_level'] = $award_level;
		$tplObj -> out['award_prize'] = $award_prize;
		$tplObj -> out['lottery_status'] = 200;
	}
}

$my_num = $redis -> setx('incr',$imsi_num,0);
$tplObj -> out['my_num'] = $my_num;
$tplObj -> out['imsi_status'] = 200;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];

$tplObj -> out['key'] = $key;
$tplObj -> display('superman_lottery.html');