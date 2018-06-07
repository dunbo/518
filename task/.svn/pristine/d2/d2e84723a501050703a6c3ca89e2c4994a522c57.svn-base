<?php

/*
	寒假活动发礼包worker
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
$worker->addFunction("vacation_lottery", "get_gift");
while ($worker->work());

function get_gift($jobs){
	global $redis;
	global $model;
	global $active_id;
	$my_need = $jobs -> workload();
	$my_need = json_decode($my_need,true);
	$imsi = $my_need[0];
	$package = $my_need[1];
	$redis_key = "vacation_lottery:{$package}_{$active_id}";
	$imsi_package = "vacation_lottery:{$imsi}_{$package}_{$active_id}";
	$imsi_gift_redis = "vacation_lottery:{$imsi}_gift_{$active_id}";
	$imsi_have_package = "vacation_lottery:{$imsi}_have_{$active_id}";
	$have_result = $redis -> gethash($imsi_have_package);
	if(!in_array($package,$have_result)){
		$the_gift_str = $redis -> rpop($redis_key);
		$the_gift = json_decode($the_gift_str,true);
		if($the_gift){
			load_helper('task');
			$task_client = get_task_client();
			$my_needs = array($imsi,$package,$the_gift['gift_num']);
			$my_need = json_encode($my_needs);
			$result = $task_client->doBackground('vacation_lottery_set',$my_need);
			$old_gift = $redis -> gethash($imsi_gift_redis);
			$imsi_gift = array('gift_num' => $the_gift['gift_num'],'package' => $package,'time' => time());
			if($old_gift){
				$old_gift[count($old_gift)+1] = $imsi_gift;
				$the_gifts = $old_gift;
			}else{
				$the_gifts = array($imsi_gift);
			}
			if($have_result){
				$have_result[count($have_result) + 1] = $package;
			}else{
				$have_result = array($package);
			}
			$redis -> sethash($imsi_have_package,$have_result);
			$redis -> sethash($imsi_gift_redis,$the_gifts);
			return $the_gift['gift_num'];
		}else{
			return 200;
		}
	}else{
		return 300;
	}
}
