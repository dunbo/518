<?php

/*
    积分兑换db处理 worker
*/
include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("integral_db", "handle");
while ($worker->work());

function handle($jobs){
    global $model;
    global $redis;
    $param = $jobs->workload();
	$param = json_decode($param,true);
	$type = $param['type'];
	$uid = $param['uid'];
	$username= $param['username'];
	$gift_number = $param['gift_number'];
	$pid = $param['pid'];
	$prizename= $param['prizename'];
	$prize_integral = $param['prize_integral'];
	$time = time();
	if($type==1){//处理库存及中奖记录
		$redis->pingConn();	
		$where = array(
			'id' =>$pid,
		);
		$data = array(
			'num' => array('exp',"`num`-1"),
			'__user_table' => 'integral_prize'
		);
		$model -> update($where,$data,'lottery/lottery');
		//echo $model->getsql();
		$award_data = array(
				'uid' => $uid,
				'username' => $username,
				'time' => $time,
				'status' => 1,
				'pid' => $pid,
				'prizename' => $prizename,
				'__user_table' => 'integral_kind_award'
		);
		$id = $model -> insert($award_data,'lottery/lottery');		
		//echo $model->getsql();
		//实物的所有兑换信息
		$arr[$id] = array(	
			'id' => $id,
			'uid' => $uid,
			'pid' => $pid ,
			'prizename' => $prizename,
			'time' => $time
		);			
		$redis -> lPush("integral_kind_award:{$uid}",json_encode($arr));
		//$gift_prize_list = $redis -> getlist("integral_kind_award:{$uid}");				
		//var_dump($gift_prize_list);
	}else if($type==2){ //礼包
			$where = array(
				'gift_number' =>$gift_number,
			);
			$data = array(
				'uid' => $uid,
				'status' => 1,
				'update_tm' => $time,
				'__user_table' => 'integral_kind_gift'
			);
			$model -> update($where,$data,'lottery/lottery');
	}
	$where = array(
		'uid' =>$uid,
	);
	$data = array(
		'deduction_integral' => array('exp',"`deduction_integral`+{$prize_integral}"),
		'update_tm' => $time,
		'__user_table' => 'integral_userinfo'
	);
	$model -> update($where,$data,'lottery/lottery');		
}
