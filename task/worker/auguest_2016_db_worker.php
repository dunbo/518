<?php

/*
    通用db处理 worker
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
$worker->addFunction("auguest_2016_db", "handle");
while ($worker->work());

function handle($jobs){
    global $model;
    $param = $jobs->workload();
	$param = json_decode($param,true);
	$type = $param['type'];
	$p_type = $param['p_type'];
	$uid = $param['uid'];
	$aid = $param['aid'];
	$username= $param['username'];
	$gift_number = $param['gift_number'];
	$pid = $param['pid'];
	$prizename= $param['prizename'];
	$table = $param['table'];
	$money= $param['money'];
	$table_gift = $param['table_gift'];
	$table_award = $param['table_award'];
	if($type==1){//处理礼包
		$where = array(
			'gift_number' =>$gift_number['gift_number'],
			'aid' => $aid
		);
		if(!empty($p_type)){
			$where['type'] = $p_type;
		
		}
		$data = array(
			'uid' => $uid,
			'aid' => $aid,
			'status' => 1,
			'update_tm' => time(),
			'__user_table' => $table_gift
		);
		$model -> update($where,$data,'lottery/lottery');
	}else if($type==2){ //处理库存及中奖记录
		$where = array(
			'id' =>$pid,
            'aid' => $aid,
		);
		$data = array(
			'num' => array('exp','`num`-1'),
			'__user_table' => $table 
		);
		$model -> update($where,$data,'lottery/lottery');
		//处理中奖表
		$now_tm= time();
		$award_data = array(
			'uid' => $uid,
			'aid' => $aid,
			'username' => $username,
			'create_tm' => $now_tm,
			'status' => 1,
			'pid' => $pid,
			'money' => $money,
			'prizename' => $prizename,
			'__user_table' => $table_award
		);
		if(!empty($p_type)){
			$award_data['type'] = $p_type;
		}
		$model -> insert($award_data,'lottery/lottery');
		echo $model->getsql();
	}
}
