<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "ask";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_GET['stop'] != 1 && !$_GET['types']){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/{$prefix}_index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
$time = time();
//@剩余抽奖次数
function get_lottery_num($uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$lottery_num = $redis->get($lottery_num_key);
	if($lottery_num === null ){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'recharge_top_userlist',
			'field' => 'sum(lottery_num) as lottery_nums,sum(deduction_num) as deduction_nums',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$lottery_num = intval($rest_list['lottery_nums'])-intval($rest_list['deduction_nums']);
		$redis->set($lottery_num_key,intval($lottery_num),15*60);	
	}	
	return $lottery_num;			
}
//获取用户抽奖的有效的用户id
function get_user_id($uid){
	global $model;	
	global $active_id;		
	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $active_id,
			'deduction_num' => 0
		),
		'table' => 'recharge_top_userlist',
		'field' => 'id,money',
		'order' => 'create_tm asc',
	);
	$rest = $model->findOne($option,'lottery/lottery');	
	return $rest;		
}

function save_deduction_num($id){
	global $model;	
	global $active_id;			
	global $time;			
	$new_data = array(
		'update_tm' => $time,
		'deduction_num' => 1,
		'__user_table' => 'recharge_top_userlist'
	);	
	$where = array('id' => $id);
	$ret =  $model->update($where, $new_data,'lottery/lottery');	
}