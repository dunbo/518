<?php

/*
    寒假抽奖活动2 奖品入库 worker
*/
include dirname(__FILE__).'/../init.php';
$active_id = 186;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("vacation_lottery_second_award", "get_award");
while ($worker->work());

function get_award($jobs){
    global $model;
    global $active_id;
    $my_need = $jobs->workload();
	$my_needs = json_decode($my_need,true);
	$imsi = $my_needs[0];
	$my_award = $my_needs[1];
	$award_data = array(
		'imsi' => $imsi,
		'award_level' => $my_award,
		'name' => '',
		'telphone' => '',
		'address' => '',
		'time' => time(),
		'status' => 0,
		'__user_table' => 'vacation_lottery_award'
	);
	$award_result = $model -> insert($award_data,'lottery/lottery');
	return ;
}