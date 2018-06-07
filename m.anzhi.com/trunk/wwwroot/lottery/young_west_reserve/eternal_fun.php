<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "eternal";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_GET['stop'] != 1 && $_GET['types'] != 1 && $_GET['types'] != 3){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/young_west_reserve/{$prefix}_index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
$time = time();
//@剩余抽奖次数
function get_rest_lottery($uid){
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
			'table' => 'valentine_draw_userinfo',
			'field' => 'draw_data_num,deduction_num',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$lottery_num = intval($rest_list['draw_data_num'])-intval($rest_list['deduction_num']);
		$redis->set($lottery_num_key,intval($lottery_num),15*60);	
	}	
	return $lottery_num;			
}

function add_lottery_num($uid){
	global $model;	
	global $active_id;	
	global $prefix;	
	global $redis;		
	$time = time();
	$where = array(
		'uid' => $uid,
		'aid' => $active_id 
	);
	$option = array(
		'where' => $where,
		'table' => 'valentine_draw_userinfo',
		'field' => 'draw_data_num,deduction_num',
	);
	$rest_list = $model->findOne($option,'lottery/lottery');	
	if($rest_list){
		$data_update = array(
			'draw_data_num' => 1,
			'update_tm' => $time,
			'username' => $_SESSION['USER_NAME'],
			'__user_table' => 'valentine_draw_userinfo',
		);
		return $model -> update($where,$data_update,'lottery/lottery');			
	}else{
		$option = array(
			'uid' => $uid,
			'aid' => $active_id,
			'draw_data_num' => 1,
			'username' => $_SESSION['USER_NAME'],
			'update_tm' => $time,
			'create_tm' => $time,
			'__user_table' => 'valentine_draw_userinfo',
		);	
		$redis->set("{$prefix}:{$active_id}_userinfo:".$uid,$option,10*86400);
		return $model->insert($option,'lottery/lottery');
	}
}

//获取实物的pid
function get_practicality_pid(){
	global $model;	
	global $active_id;			
	$option = array(
		'where' => array(
			'aid' => $active_id,
			'level' => 1
		),
		'table' => 'valentine_draw_prize',
		'field' => 'id',
		'cache_time' => 15*60		
	);
	$rest = $model->findOne($option,'lottery/lottery');		
	return $rest['id'];
}