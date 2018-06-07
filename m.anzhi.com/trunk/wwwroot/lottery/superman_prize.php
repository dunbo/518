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
$imsi = $_SESSION['USER_IMSI'];

$key_actsid=get_key();
if($key_actsid)
{
	$info= $redis -> get($key_actsid);
	$info_arr=json_decode($info, true);
	$info_arr['get_prize']=1;
	$save_info=$redis -> set($key_actsid,json_encode($info_arr));
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

if($all_award_result){
	foreach($all_award_result as $key => $val){
		$award_info_option = array(
			'where' => array(
				'pid' => $val['pid']
			),
			'cache_time' => 86400,
			'table' => 'gm_lottery_prize'
		);
		$award_info_result = $model -> findOne($award_info_option,'lottery/lottery');
		$val['award'] = $award_info_result['name'];
		$val['the_time'] = date('Y-m-d',$val['time']);
		$val['telephone'] = substr_replace($val['telephone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
	$tplObj -> out['all_award_result'] = $all_award_result;
	$tplObj -> out['all_award_count'] = count($all_award_result);
}

$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 1
	),
	'order' => 'time DESC',
	'table' => 'superman_lottery_award'
);
$result = $model -> findAll($option,'lottery/lottery');

$content_option = array(
	'where' => array(
		'config_type' => 'SUPERMAN_AWARD',
		'status' => 1
	),
	'table' => 'pu_config'
);

$content_result = $model -> findOne($content_option);
$award_content = json_decode($content_result['configcontent'],true);
foreach($result as $key => $val){
	$val['level'] = $award_content[$val['award_level']][0];
	$val['prize'] = $award_content[$val['award_level']][1];
	$val['the_time'] = date('Y-m-d',$val['time']);
	$result[$key] = $val;
}

$tplObj -> out['key'] = $key_actsid;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['result'] = $result;
$tplObj -> display("superman_prize.html");