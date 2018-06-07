<?php

/*
	寒假活动发礼包入库worker
*/
include dirname(__FILE__).'/../init.php';
$active_id = 185;
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
$worker->addFunction("vacation_lottery_set", "set_gift");
while ($worker->work());

function set_gift($jobs){
	global $redis;
	global $model;
	global $active_id;
	$my_need = $jobs -> workload();
	$my_need = json_decode($my_need,true);
	$imsi = $my_need[0];
	$package = $my_need[1];
	$gift = $my_need[2];
	
	$option = array(
		'where' => array(
			'package' => $package,
			'gift_num' => $gift,
			'status' => 0
		),
		'limit' => 1,
		'table' => 'vacation_gift_list'
	);
	$result = $model -> findOne($option,'lottery/lottery');
	
	$where = array(
		'id' => $result['id'],
		'package' => $package,
		'status' => 0,
		'gift_num' => $gift
	);
	$data = array(
		'imsi' => $imsi,
		'status' => 1,
		'time' => time(),
		'__user_table' => 'vacation_gift_list'
	);
	$result = $model -> update($where,$data,'lottery/lottery');
	return;
}