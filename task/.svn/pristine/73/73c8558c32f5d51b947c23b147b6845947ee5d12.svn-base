<?php
/*
 *   9月一元购活动
 */
include dirname(__FILE__).'/../init.php';
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
$worker->addFunction("one_dollar_worker", "get_award");
while ($worker->work());

get_award();
function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
    $uid = $jobs['uid'];            
	$username= $jobs['username'];
	$pid = $jobs['pid'];     
	$num = $jobs['num'];   
	$position = $jobs['position'];   
	$prize_integral = intval($jobs['prize_integral']);   
	$redis->pingConn();	          
	//剩余积分
	$rest_integral = get_rest_integral($uid);
	//剩余人次
	$res_participant = $redis->gethash("one_dollar_prize:{$position}","res_participant");
	$prize_num = $redis->gethash("one_dollar_prize:{$position}","num");
	$start_time = $redis->gethash("one_dollar_prize:{$position}","start_time");
	if($num == '包尾'){
		$num = $res_participant;
	}
	if($rest_integral <= 0 || $rest_integral < $num){
		 $ret_arr = array(
			'code' => 0,
			'msg' => '您的积分不足，请您充值后再抢夺！',
		 );
		 return json_encode($ret_arr);			
	}
	if($prize_num == 0){
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
	if($num > $res_participant){
		//输入的人次大于剩余的人次
		 $ret_arr = array(
			'code' => 0,
			'msg' => '您购买的剩余人次数不足',
			'res_participant' => $res_participant,
		 );
		 return json_encode($ret_arr);			
	}
	$now_res_participant = $redis->setx("HINCRBY","one_dollar_prize:{$position}","res_participant",-(intval($num)));
	if($now_res_participant < 0){ 
		$redis->setx("HSET","one_dollar_prize:{$position}","res_participant", $res_participant);
		 $ret_arr = array(
			'code' => 0,
			'msg' => '您购买的剩余人次数不足'
		 );
		 return json_encode($ret_arr);		
	}
	$jobs['num'] = $num;
	$data = array(
		'prizename' => $jobs['prizename'],
		'pid' => $pid,
		'prize_integral' => $prize_integral,
	);
	if($num == $prize_integral){
		 $now_num = $redis->setx("HINCRBY","one_dollar_prize:{$position}","num",-1);
		 if($now_num < 0){
			 $redis->setx("HSET","one_dollar_prize:{$position}","num",0);
			 $ret_arr = array(
				'code' => 0,
				'msg' => '该奖品已经被兑换完'
			 );
			 return json_encode($ret_arr);	
		 }		
		//奖品数减1
		save_prize_db($pid);
		$jobs['status'] = 1;
		one_dollar_db($jobs);
		//刷奖品位缓存
		brush_cache($position,$data);
		$resarr = array(
			'code' => 1,
			'pid' => $pid,
			'position' => $position,
			'uid' => $uid,
			'num' => $num,
			'status' => 1,
		);		
	} else{
		one_dollar_db($jobs);		
		if($now_res_participant == 0){
			$now_num = $redis->setx("HINCRBY","one_dollar_prize:{$position}","num",-1);
			if($now_num < 0){
				 $redis->setx("HSET","one_dollar_prize:{$position}","num",0);
				 $ret_arr = array(
					'code' => 0,
					'msg' => '该奖品已经被兑换完'
				 );
				 return json_encode($ret_arr);	
			}	
			//抽奖
			$task_client = get_task_client();
			$param = array(
				'start_time' => $start_time,
				'pid' => $pid,
			);
			$the_award = $task_client->do('one_dollar_lottery',json_encode($param));
			$lottery_rs = json_decode($the_award,true);	
			$redis->setx("HSET","one_dollar_prize:{$position}","num",0);
			//奖品数减1
			save_prize_db($lottery_rs['pid']);
			//刷奖品位缓存
			brush_cache($position,$data);
			$jobs['uid'] = $lottery_rs['uid'];
			$jobs['pid'] = $lottery_rs['pid'];
			$jobs['status'] = 1;
			$jobs['num'] = 0;
			one_dollar_db($jobs);				
			$resarr = array(
				'code' => 1,
				'pid' => $lottery_rs['pid'],
				'position' => $position,
				'uid' => $lottery_rs['uid'],
				'num' => $num,
				'status' => 1,
			);	
		}else{
			$resarr = array(
				'code' => 1,
				'pid' => $pid,
				'position' => $position,
				'uid' => $uid,
				'num' => $num,
			);			
		}
	}
	//剩余积分
	$now_rest = $redis->setx('incr','one_dollar_rest_integral'.$uid, -(intval($num)));	
	//已参与人次
	$redis->setx("HINCRBY","one_dollar_prize:{$position}","participants",+(intval($num)));	
	return json_encode($resarr);		
}
//@积分剩余量
function get_rest_integral($uid){
	global $model;
	global $redis;		
	//$redis->delete('rest_integral'.$uid);
	$rest = $redis->get('one_dollar_rest_integral'.$uid);
	if($rest === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'one_dollar_userinfo',
			'field' => 'integral_num,deduction_integral',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$rest = $rest_list['integral_num']-$rest_list['deduction_integral'];
		$redis->set('one_dollar_rest_integral'.$uid,intval($rest),15*60);
	}	
	return $rest;			
}
//db操作
function one_dollar_db($data){
	global $model;
	global $redis;		
	$time = time();	
	$where = array(	
		'uid' => $data['uid'],
		'pid' => $data['pid'],
		'start_time' => $data['start_time'],
	);	
	$option = array(
		'where' => $where,
		'table' => 'one_dollar_kind_award',
		'field' => 'id,integral',		
	);
	$res = $model->findOne($option,'lottery/lottery');
	$chances_str = ($res['integral']+$data['num'])."/".$data['prize_integral'] ;
	if($res){
		$map = array(
			'integral' => array('exp',"`integral`+{$data['num']}"),
			'probability' => $chances_str,
			'status' => $data['status'] ? $data['status'] : 0,
			'__user_table' => 'one_dollar_kind_award'
		);
		$model -> update($where,$map,'lottery/lottery');		
	}else{
		$award_data = array(
			'uid' => $data['uid'],
			'time' => $time,
			'status' => $data['status'] ? $data['status'] : 0,
			'pid' => $data['pid'] ,
			'prizename' => $data['prizename'],
			'username' => $data['username'],
			'integral' => $data['num'],
			'start_time' => $data['start_time'],
			'probability' =>  $chances_str,
			'__user_table' => 'one_dollar_kind_award'
		);	
		$id = $model -> insert($award_data,'lottery/lottery');		
		//echo $model->getsql();
		//实物的所有兑换信息
		$arr[$id] = array(	
			'id' => $id,
			'uid' => $data['uid'],
			'pid' => $data['pid'] ,
			'prizename' => $data['prizename'],
			'status' => $data['status'],
		);		
		$redis -> lPush("one_dollar_kind_award:{$uid}",json_encode($arr));	
	}
	$where = array(
		'uid' => $data['uid'],
	);
	$map = array(
		'deduction_integral' => array('exp',"`deduction_integral`+{$data['num']}"),
		'update_tm' => $time,
		'__user_table' => 'one_dollar_userinfo'
	);
	$model -> update($where,$map,'lottery/lottery');			
}

function save_prize_db($pid){
	global $model;	
	$where = array(	
		'id' => $pid ,
		'num' => array('exp','>0')
	);
	$data = array(
		'num' => array('exp',"`num`-1"),
		'__user_table' => 'one_dollar_prize'
	);
	$model -> update($where,$data,'lottery/lottery');	
}
//刷缓存
function brush_cache($position,$jobs){
	global $model;
	global $redis;		
	$option = array(
		'table' => 'one_dollar_prize',
		'where' => array(
			'position' => $position,
			'num' => array('exp'," >0"),
		),
		'order' => 'rank asc', 
		'field' => 'id,prize_integral,name,start_time,refresh_time',
	);
	$prize_list = $model->findOne($option,'lottery/lottery');
	if($prize_list['id'] > 4){
		$where = array(	
			'id' => $prize_list['id'] ,
			'num' => array('exp','>0')
		);
		$prize_list['start_time'] = time()+($prize_list['refresh_time']*3600);
		$data = array(
			'start_time' => $prize_list['start_time'],
			'__user_table' => 'one_dollar_prize'
		);
		$model -> update($where,$data,'lottery/lottery');	
		
	}
	if($prize_list){	
		$prize_info = array(
			'id' => $prize_list['id'],//奖品id
			'num' => 1,//数量
			'sold_out' => 1,//售馨标识
			'position' => $position,//位置
			'prizename' => $prize_list['name'],//奖品名称
			'start_time' => intval($prize_list['start_time']),//抢夺开始时间
			'refresh_time' => $prize_list['refresh_time'],//刷新频率
			'prize_integral' => intval($prize_list['prize_integral']),//总积分（总人次）
			'participants' => 0,//已参与人次
			'res_participant' => intval($prize_list['prize_integral']),//剩余人次
			'old_prizename' => $jobs['prizename'],
			'old_id' => $jobs['pid'],
			'old_prize_integral' => $jobs['prize_integral'],
		);
		$redis->sethash("one_dollar_prize:{$position}",$prize_info,86400*10);	
	}
}