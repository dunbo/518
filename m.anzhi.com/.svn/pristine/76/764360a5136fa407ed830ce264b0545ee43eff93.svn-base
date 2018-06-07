<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$aid=$_GET['aid'];
$key_actsid=get_key();
if($key_actsid)
{
	$info= $redis -> get($key_actsid);
	$info['get_end'] =1;
	$info['url']="http://118.26.203.23/lottery/christmas_lottery.php";
	$save_info=$redis -> set($key_actsid,$info);
}

$model = new GoModel();
$award_level_option = array(
	'where' => array(
		'config_type' => 'CHRISTMAS_AWARD',
		'status' => 1
	),
	'table' => 'pu_config'
);
$award_level_result = $model -> findOne($award_level_option);
$award_arr = json_decode($award_level_result['configcontent'],true);
$award_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'award',
	'table' => 'christmas_award'
);
$award_result = $model -> findAll($award_option,'lottery/lottery');
foreach($award_result as $key => $val){
	$val['prize'] = $award_arr[$val['award'] - 1][1];
	$val['the_telphone'] = substr_replace($val['telphone'],'****',3,4);
	$award_results[$val['prize']][] = $val;
}
$tplObj -> out['award_result'] = $award_results;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['actsid'] = $key_actsid;
$tplObj -> display("christmas_lottery_end.html");