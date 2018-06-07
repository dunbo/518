<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "h5_gm";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
//获取host
$activity_host = $configs['activity_url'];

$time = time();
function get_gift_list(){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$key = "{$prefix}:{$active_id}:prize_list";
	$prize_arr = $redis->get($key);	
	if(empty($prize_arr)){
		$option = array(
			'where' => array(
				'aid'	=>	$active_id, 
				'status' => 1,
			),
			'table' => 'valentine_draw_prize',
			'field' => 'id,level,name,num,type,`desc`',
			'order' => 'level asc'
		);
		$prize_list= $model->findAll($option,'lottery/lottery');
		$prize_arr = array();
		foreach($prize_list as $k => $v){
			list($pkg,$pic_name) = explode(':',$v['desc']);
			$prize_arr[$v['level']]['desc'] = $pkg;
			$prize_arr[$v['level']]['pic_name'] = $pic_name;
			$prize_arr[$v['level']]['num'] = $v['num'];
			$prize_arr[$v['level']]['name'] = $v['name'];
			$prize_arr[$v['level']]['pid'] = $v['id'];
		}
		$redis->set($key,$prize_arr,60*86400);	
	}	
	return 	$prize_arr;	
}
//获取是否获取状态
function get_is_lottery($uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;			
	$is_lottery_key = "{$prefix}:{$active_id}:is_lottery:".$uid;
	$is_lottery = $redis->get($is_lottery_key);
	if($is_lottery === null ){	
		$option = array(
			'where' => array(
				'uid'	=>	$uid,
				'aid' => $active_id,
				'status' => 1,
			),
			'table' => 'valentine_draw_gift',
			'field' => 'pid,gift_number',
			'group' => 'pid',
		);
		$ret= $model->findAll($option,'lottery/lottery');
		if($ret){
			$is_lottery = array();
			foreach($ret as $k => $v){
				$is_lottery[$v['pid']] = 1;
			}
			$redis->set($is_lottery_key,$is_lottery,60*86400);
		}
	}
	return $is_lottery;
}
//
function get_gift_list_old($position){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$key = "{$prefix}:{$active_id}_prize_rank:".$position;
	$prize_arr = $redis->get($key);
	if(empty($prize_arr)){
		$option = array(
			'where' => array(
				'level'	=>	$position,
				'aid'	=>	$active_id, 
			),
			'table' => 'valentine_draw_prize',
			'field' => 'id,level,name,num,type,`desc`',
			'order' => 'level asc'
		);
		$prize_arr= $model->findOne($option,'lottery/lottery');
		list($pkg,$pic_name) = explode(':',$prize_arr['desc']);
		$prize_arr['desc'] = $pkg;
		$prize_arr['pic_name'] = $pic_name;		
		$redis->set($key,$prize_arr,1200);
		$prize_num = intval($prize_arr['num']);
		$prize_num_key = "{$prefix}:{$active_id}_prize_num:".$position;
		$redis->set($prize_num_key,$prize_num,1200);
	}
	return 	$prize_arr;
}
//获取是否获取状态
function get_is_lottery_old($position,$pid,$uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;			
	$is_lottery_key = "{$prefix}:{$active_id}:is_lottery:".$position.":".$uid;
	$is_lottery = $redis->get($is_lottery_key);
	if(!$is_lottery){
		$option = array(
			'where' => array(
				'uid'	=>	$uid,
				'pid'	=>	$pid, 
				'status' => 1,
			),
			'table' => 'valentine_draw_gift',
			'field' => 'id,gift_number',
		);
		$ret= $model->findOne($option,'lottery/lottery');
		if($ret){
			$is_lottery = 1;
			$redis->set($is_lottery_key,1,60*86400);
		}
	}
	return $is_lottery;
}

function get_prize_count(){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;			
	$prize_count_key = "{$prefix}:{$active_id}:prize_count";
	$prize_count = $redis->get($prize_count_key);
	if(!$prize_count){
		$option = array(
			'where' => array(
				'status' => 1,
				'aid' => $active_id
			),
			'table' => 'valentine_draw_prize',
			'field' => 'count(*) as total',
		);
		$ret= $model->findOne($option,'lottery/lottery');
		$prize_count = $ret['total'];
		$redis->set($prize_count_key,$prize_count,60*30);

	}
	return $prize_count;	
}