<?php
/*
 *   签到抽奖worker
 */
include_once (dirname(realpath(__FILE__)).'/../../init.php');
// include dirname(__FILE__).'/../../init.php';
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
$worker->addFunction("hszzsign", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	            
	$prefix = $jobs['prefix'];	
	$uid = $jobs['uid'];	
	$aid = $jobs['aid'];	
	$username= $jobs['username'];
	$package= $jobs['package'];
	$table =  $jobs['table'];
	$table_gift =  $jobs['table_gift'];
	$table_award =  $jobs['table_award'];
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$res = $redis->get("{$prefix}:{$aid}_prize_name:1");
	$gift_base = $redis->get("{$prefix}:{$aid}_base_list");
	if(empty($res) || empty($gift_base)){
		$option = array(
			'where' => array(
					'num' => array('exp','>0'),
					'aid' => $aid, //通用 活动ID
			),
			'table' => $table,
			//'table' => 'valentine_draw_prize',
		);
		$prize_arr= $model->findAll($option,'lottery/lottery');
		$gift_base = array();
		foreach($prize_arr as $v){
			$id = $v['id'];
			$num = intval($v['num']);
			$name = $v['name'];
			$level = intval($v['level']);
			$gift_base[$v['id']] = $num;
			$redis->set("{$prefix}:{$aid}_prize_num:".$id,$num,1200);
			$redis->set("{$prefix}:{$aid}_prize_name:".$id,$name,1200);
			$redis->set("{$prefix}:{$aid}_prize_rank:".$id,$level,1200);
		}
		$redis->set("{$prefix}:{$aid}_base_list",$gift_base,1200);
	}
	$pid = lottery($gift_base,5000);
	  
	//中奖，检查数量 如数量不足 返回未中奖
	if($pid!=-1){
		// 奖品数量-1
		$now_num = $redis -> setx('incr',"{$prefix}:{$aid}_prize_num:".$pid, -1);
		if ($now_num < 0) {
			$resarr = array();
			$resarr['pid'] = 0;
			print_r($resarr);
			return json_encode($resarr);
		}
		$prizename = $redis -> get("{$prefix}:{$aid}_prize_name:".$pid);	
		$prize_rank = $redis->get("{$prefix}:{$aid}_prize_rank:".$pid);
		//减少库中实际数量
	
		$param =array(
			'type' => 2,
			'uid' => $uid,
			'aid' => $aid,
			'pid' => $pid,
			'level' => $prize_rank,
			'username' => $username,
			'prizename' => $prizename,
			'table' => $table,
			'table_award' => $table_award,
			//'table_award' => 'valentine_draw_award',
		);
		$task_client = get_task_client();
		$task_client->doBackground('recharge_top_db',json_encode($param));
		
		$resarr = array(
			'pid' => $pid,
			'prize_rank' => $prize_rank,
			'prizename' => $prizename,
		);
		print_r($resarr);
		return json_encode($resarr);
	}else{
		$resarr = array();
		$resarr['pid'] = 0;
		print_r($resarr);
		return json_encode($resarr);
	}
}


function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    if(!$gift_base){
        return -1;
    }
    foreach ($gift_base as $k=>$v) {
        $gift_line[$k] = array($nows+1, $nows+$v);
        $nows += $v;
    }
    $rand = mt_rand(1, $sum);
    foreach ($gift_line as $k => $v) {
        if ($rand >= $v[0] && $rand <= $v[1]) {
            return $k;
        }
    }
    return -1;
}
