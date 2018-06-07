<?php
/*
 *   h5专题活动领取取礼包worker
 *   
 */
include dirname(__FILE__).'/../../init.php';
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
$worker->addFunction("dota_sign", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);
	$prefix = $jobs['prefix'];	
	$uid = $jobs['uid'];
	$aid = $jobs['aid'];
	$package= $jobs['package'];
	$position = $jobs['position'];
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$key = "{$prefix}:{$aid}_prize_rank:".$position;
	$prize_arr = $redis->get($key);
	$prize_num_key = "{$prefix}:{$aid}_prize_num:".$position;
	if(empty($prize_arr)){
		$option = array(
			'where' => array(
				'level'	=>	$position,
				'aid'	=>	$aid, //通用 活动ID
			),
			'table' => 'valentine_draw_prize',
			'field' => 'id,`level`,`name`,num,`type`,`desc`'
		);
		$prize_arr= $model->findOne($option,'lottery/lottery');
		$redis->set($key,$prize_arr,1200);
		$redis->set($prize_num_key,intval($prize_arr['num']),1200);
	}

	// 奖品数量-1
	$now_num = $redis -> setx('incr',$prize_num_key, -1);
	echo 'now_num:'.$now_num."\n";
	if ($now_num < 0) {
		$ret_arr = array(
			'code'	=>	0,
			'msg'	=>	'剩余奖品不足',
		);
		return json_encode($ret_arr);		
	}
	$pid = $prize_arr['id'];
	$gift_key = "activity_gift:{$aid}:{$pid}:gift_num:{$package}";
	//$gift_key = "custom:{$aid}:{$pid}_gift:{$package}";
    $gift_number = $redis -> rpop($gift_key);
    if(empty($gift_number)){
        $gift_number = $redis -> rpop($gift_key);
	}	
	$gift_number = json_decode($gift_number,true);
    if(empty($gift_number)){
		$ret_arr = array(
			'code'	=>	0,
			'msg'	=>	'礼包不足',
		);
		return json_encode($ret_arr);	
	}		
	$where = array(
		'id' =>$pid,
		'aid' => $aid,
	);
	$data = array(
		'num' => array('exp','`num`-1'),
		'__user_table' => 'valentine_draw_prize',
	);
	$model -> update($where,$data,'lottery/lottery');			
	$where = array(
		'gift_number' => $gift_number['gift_number'],
		'aid' => $aid,
		'pid' => $pid,
	);
	$data = array(
		'uid' => $uid,
		'aid' => $aid,
		'pid' => $pid,
		'status' => 1,
		'update_tm' => time(),
		'__user_table' => 'valentine_draw_gift'
	);
	$model -> update($where,$data,'lottery/lottery');	
	$resarr = array(
		'code' => 1,
		'prizename' => $prize_arr['name'],
		'gift_number' => $gift_number['gift_number'],
	);
	print_r($resarr);
	return json_encode($resarr);		
}

