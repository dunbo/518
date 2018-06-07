<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$a = "general_lottery:virtual_182";
$b = $redis -> getlist($a);
var_dump($b);exit;

$page_option = array(
	'where' => array(
		'activate_type' => 4,
		'ap_type' => 2,
		'status' => 1
	),
	'table' => 'sj_activity_page'
);

$page_result = $model -> findAll($page_option);

foreach($page_result as $key => $val){
	$pid_str_go .= $val['ap_id'].',';
}
$pid_str = substr($pid_str_go,0,-1);
$time = strtotime(date('Ymd 00:00:00',time()));
$activity_option = array(
	'where' => array(
		'activity_page_id' => array('exp',"in ({$pid_str})"),
		'end_tm' => array('exp'," > {$time}"),
		'status' => 1
	),
	'field' => 'id',
	'table' => 'sj_activity'
);

$activity_result = $model -> findAll($activity_option);

foreach($activity_result as $key => $val){
	$activity_str_go .= $val['id'].',';
}
$activity_str = substr($activity_str_go,0,-1);

$prize_option = array(
	'where' => array(
		'aid' => array('exp',"in ({$activity_str})"),
		'status' => 1,
		'type' => 2
	),
	'field' => 'pid',
	'table' => 'gm_lottery_prize'
);
$prize_result = $model -> findAll($prize_option,'lottery/lottery');

foreach($prize_result as $key => $val){
	$virtual_option = array(
		'where' => array(
			'pid' => $val['pid'],
			'status' => 0
		),
		'field' => 'first_text,second_text,third_text',
		'table' => 'gm_virtual_prize'
	);
	$virtual_result = $model -> findAll($virtual_option,'lottery/lottery');
	if($virtual_result){
		$virtual_results[] = $virtual_result;
		$virtual_prize = "general_lottery:virtual_{$val['pid']}";
		$have_prize = $redis -> getx('llen',$virtual_prize);
		if(!$have_prize){
			$redis -> setlist($virtual_prize,$virtual_result);
		}
	}
}
$a = $redis -> getlist("general_lottery:virtual_95");
