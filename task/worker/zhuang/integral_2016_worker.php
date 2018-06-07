<?php
/*
 *   5月底积分兑换活动（2016）
 */
include dirname(__FILE__).'/../../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$prefix = "xy2_2016";
$gift_base = array();
ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("integral_2016_worker", "get_award");
while ($worker->work());

get_award();
function get_award($jobs){
	global $model;
	global $redis;
	global $prefix;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
    $uid = $jobs['uid'];            
	$username= $jobs['username'];
	$pid = $jobs['pid'];     
	$active_id = $jobs['aid'];     
	$num = $jobs['num'];   
	$position = $jobs['position'];   
	$prize_integral = intval($jobs['prize_integral']);   
	$redis->pingConn();	    
	if(!$uid || !$active_id){
		 $ret_arr = array(
			'code' => 0,
			'msg' => '页面超时',
		 );
		 return json_encode($ret_arr);				
	}	
	//剩余积分
	$rest_integral = get_rest_integral($uid,$active_id);
	//剩余人次
	$key = "{$prefix}:{$active_id}:prize:{$position}";
	$res_num = $redis->gethash($key,"res_num");
	$start_time = $redis->gethash($key,"start_time");
	if($rest_integral <= 0 || $rest_integral < $num*$prize_integral){
		 $ret_arr = array(
			'code' => 0,
			'msg' => '您的积分不足，请您充值后再抢夺！',
		 );
		 return json_encode($ret_arr);			
	}
	if($res_num == 0){
		$ret_arr = array(
			'code' => 0,
			'msg' => '该奖品已经被兑换完'
		 );
		 return json_encode($ret_arr);		
	}
	if($start_time > time()){
		$ret_arr = array(
			'code' => 0,
			'msg' => '该奖品抢夺时间未开始'
		 );
		 return json_encode($ret_arr);		
	}
	if($num > $res_num){
		//输入的人次大于剩余的人次
		 $ret_arr = array(
			'code' => 0,
			'msg' => '您兑换的剩余数不足',
			'res_num' => $res_num,
		 );
		 return json_encode($ret_arr);			
	}
	$now_res_num = $redis->setx("HINCRBY",$key,"res_num",-(intval($num)));
	if($now_res_num < 0){ 
		$redis->setx("HSET",$key,"res_num", $res_num);
		 $ret_arr = array(
			'code' => 0,
			'msg' => '您兑换的剩余数不足'
		 );
		 return json_encode($ret_arr);		
	}
	$jobs['num'] = $num;
	$data = array(
		'prizename' => $jobs['prizename'],
		'pic_name' => $jobs['pic_name'],
		'pid' => $pid,
		'aid' => $active_id,
		'prize_integral' => $prize_integral,
	);
	//已参与人次
	$redis->setx("HINCRBY",$key,"participants",+(intval($num)));	
	//奖品数减1
	save_prize_db($pid,$active_id,$num);
	one_dollar_db($jobs);
	if($now_res_num == 0){
		//刷奖品位缓存
		brush_cache($position,$data);		
	}
	//剩余积分
	$redis->setx('incr',"{$prefix}:".$active_id.":res_integral:".$uid, -(intval($num*$prize_integral)));	
	$resarr = array(
		'code' => 1,
		'pid' => $pid,
		'position' => $position,
		'uid' => $uid,
		'num' => $num,
	);		
	return json_encode($resarr);		
}
//@积分剩余量
function get_rest_integral($uid,$active_id){
	global $model;
	global $redis;		
	global $prefix;		
	//$redis->delete('rest_integral'.$uid);
	$integral_key = "{$prefix}:".$active_id.":res_integral:".$uid;
	$rest = $redis->get($integral_key);
	if($rest === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id,
			),
			'table' => 'one_dollar_userinfo',
			'field' => 'integral_num,deduction_integral',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$rest = $rest_list['integral_num']-$rest_list['deduction_integral'];
		$redis->set($integral_key,intval($rest),15*60);
	}	
	return $rest;			
}
//db操作
function one_dollar_db($data){
	global $model;
	global $redis;		
	global $prefix;		
	$time = time();	
	$where = array(	
		'uid' => $data['uid'],
		'pid' => $data['pid'],
		'aid' => $data['aid'],
		'start_time' => $data['start_time'],
	);	
	$option = array(
		'where' => $where,
		'table' => 'one_dollar_kind_award',
		'field' => 'id,integral',		
	);
	$res = $model->findOne($option,'lottery/lottery');
	if($res){
		$map = array(
			'integral' => array('exp',"`integral`+{$data['num']}"),
			'status' => 1,
			'__user_table' => 'one_dollar_kind_award'
		);
		$model -> update($where,$map,'lottery/lottery');		
	}else{
		$award_data = array(
			'uid' => $data['uid'],
			'aid' => $data['aid'],
			'time' => $time,
			'status' => 1,
			'pid' => $data['pid'] ,
			'prizename' => $data['prizename'],
			'username' => $data['username'],
			'integral' => $data['num'],
			'start_time' => $data['start_time'],
			'__user_table' => 'one_dollar_kind_award'
		);	
		$id = $model -> insert($award_data,'lottery/lottery');		
		//echo $model->getsql();
		//用户的抢夺信息
		// $arr[$id] = array(	
			// 'id' => $id,
			// 'uid' => $data['uid'],
			// 'pid' => $data['pid'] ,
			// 'prizename' => $data['prizename'],
			// 'status' => 1,
			// 'position' => $data['position'],
		// );		
		// $redis -> lPush("{$prefix}:{$data['aid']}:kind_award:{$data['uid']}",json_encode($arr));	
	}
	$redis->delete("{$prefix}:{$data['aid']}:kind_award:{$data['uid']}");
	$where = array(
		'uid' => $data['uid'],
		'aid' => $data['aid'],
	);
	$deduction_integral = $data['num']*$data['prize_integral'];
	$map = array(
		'deduction_integral' => array('exp',"`deduction_integral`+{$deduction_integral}"),
		'update_tm' => $time,
		'__user_table' => 'one_dollar_userinfo'
	);
	$model -> update($where,$map,'lottery/lottery');			
}

function save_prize_db($pid,$active_id,$num){
	global $model;	
	$where = array(	
		'id' => $pid ,
		'aid' => $active_id,
		'num' => array('exp','>0')
	);
	$data = array(
		'num' => array('exp',"`num`-{$num}"),
		'__user_table' => 'one_dollar_prize'
	);
	$model -> update($where,$data,'lottery/lottery');	
}
//刷缓存
function brush_cache($position,$jobs){
	global $model;
	global $redis;		
	global $prefix;		
	$option = array(
		'table' => 'one_dollar_prize',
		'where' => array(
			'position' => $position,
			'aid' => $jobs['aid'],
			'num' => array('exp'," >0"),
		),
		'order' => 'rank asc', 
	);
	$prize_list = $model->findOne($option,'lottery/lottery');
	if($prize_list){	
		if($prize_list['refresh_time'] > 0){
			$where = array(	
				'id' => $prize_list['id'] ,
				'aid' => $jobs['aid'] ,
				'num' => array('exp','>0')
			);
			$prize_list['start_time'] = time()+($prize_list['refresh_time']*3600);
			$data = array(
				'start_time' => $prize_list['start_time'],
				'__user_table' => 'one_dollar_prize'
			);
			$model -> update($where,$data,'lottery/lottery');
		}		
		$key = "{$prefix}:{$jobs['aid']}:prize:{$position}";	
		$prize_info = array(			
			'id' => $prize_list['id'],//奖品id
			'position' => intval($position),//位置
			'num' => intval($prize_list['num']),//数量
			'prizename' => $prize_list['name'],//奖品名称
			'start_time' => $prize_list['start_time'],//抢夺开始时间
			'refresh_time' => intval($prize_list['refresh_time']),//刷新频率
			'prize_integral' => intval($prize_list['prize_integral']),//总积分（总人次）
			'participants' => 0,//已参与人次
			'res_num' => intval($prize_list['num']),//剩余人次
			'pic_name' => $prize_list['pic_name'],//图片文件名
			'sold_out' => 1,//售馨标识
		);
		$redis->sethash($key,$prize_info,86400*10);	
	}
}
