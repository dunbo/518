<?php
/*
 *   积分兑换worker
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
$worker->addFunction("integral_work", "get_award");
while ($worker->work());

get_award();
function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
    $uid = $jobs['uid'];            
	$username= $jobs['username'];
	$redis->pingConn();	
	if($jobs['type'] == 1){//实物
		$rank = $jobs['rank'];   
		//实物数减一
		 $now_num = $redis->setx("HINCRBY","integral_prize:{$rank}","num",-1);
		 if($now_num < 0){
			 $redis->setx("HSET","integral_prize:{$rank}","num",0);
			 $ret_arr = array(
				'code' => 0,
				'msg' => '该奖品已经被兑换完'
			 );
			 return json_encode($ret_arr);	
		 }
		//每天的剩余次数减一
		$redis->setx('incr','integral_exchange_num'.$uid,-1);

		$prize_list = $redis->gethash("integral_prize:{$rank}");
		$prize_integral = intval($prize_list['prize_integral']);            
		//剩余积分
		$redis->setx('incr','rest_integral'.$uid, -($prize_integral));	
		$prizename = $prize_list['name'];            
		$param =array();
		$param['type'] = 1;
		$param['uid'] = $uid;
		$param['username'] = $username;
		$param['pid'] = $prize_list['id'];
		$param['prizename'] = $prizename;
		$param['prize_integral'] = $prize_integral; //奖品积分
		$task_client = get_task_client();
		$task_client->doBackground('integral_db',json_encode($param));
		$resarr = array(
			'code' => 1,
			'pid' => $prize_list['id'],
			'prizename' => $prizename,
			'uid' => $uid,
		);
		return json_encode($resarr);		
	}else{
		$pkg = $jobs['package'];
		//处理  redis 的礼包
		$gift_list = $redis -> rpop("integral_gift:{$pkg}");
		if(empty($gift_list)){
			 $ret_arr = array(
				'code' => 0,
				'msg' => '包名'.$pkg.'礼包已被领完'
			 );
			 return json_encode($ret_arr);	
		}
		$gift_list = json_decode($gift_list,true);
		$gift_number = $gift_list['gift_number'];
		//每天的剩余次数减一
		$redis->setx('incr','integral_exchange_num'.$uid,-1);
		//剩余积分
		$redis->setx('incr','rest_integral'.$uid,-20);
		//礼包的剩余次数减一
		$redis->setx('incr','integral_surplus_gift_num',-1);		
		//用户中奖信息
		$arr[$gift_number] = array(	
			'gift_number' => $gift_number,
			'uid' => $uid,
			'package' => $gift_list['package'] ,
			'softname' => $gift_list['softname'],
			'update_tm' => time(),
		);
		//礼包的所有兑换信息
		$redis -> lPush("integral_gift_prize:{$uid}",json_encode($arr));
		//$gift_prize_list = $redis -> getlist("integral_gift_prize:{$uid}");
		$param =array();
		$param['type'] = 2;
		$param['uid'] = $uid;
		$param['prize_integral'] = 20; //奖品积分
		$param['gift_number'] = $gift_number;
		$task_client = get_task_client();
		$task_client->doBackground('integral_db',json_encode($param));
		$resarr = array();
		$resarr['code'] = 1;
		$resarr['uid'] = $uid;
		$resarr['gift_number'] = $gift_number;
		return json_encode($resarr);		
	}
}


