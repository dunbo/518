<?php
/*
 *   通用的imsi标记的抽奖worker
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
$worker->addFunction("custom_imsi_lottery", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);             
	$prefix = $jobs['prefix'];	
	$aid = $jobs['aid'];	
	$imsi = $jobs['imsi'];
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$gift_base = $redis->get("{$prefix}:{$aid}_base_list");
	$basenum = $redis->get("{$prefix}:{$aid}_basenum");
	if(empty($basenum) || empty($gift_base)){
		$option = array(
			'where' => array(
				'num' => array('exp','>0'),
				'probability' => array('exp','!=0'),
				'aid' => $aid, //通用 活动ID
				'status' => 1,
			),
			'table' => 'valentine_draw_prize',
		);
		$prize_arr= $model->findAll($option,'lottery/lottery');
		$gift_base = array();
		foreach($prize_arr as $v){
			$id = $v['id'];
			$num = intval($v['num']);
			$name = $v['name'];
			$level = intval($v['level']);
			$type = intval($v['type']);	
			$redis->set("{$prefix}:{$aid}_prize_type:".$id,$type,1200);
			$redis->set("{$prefix}:{$aid}_prize_num:".$id,$num,1200);
			$redis->set("{$prefix}:{$aid}_prize_name:".$id,$name,1200);
			$redis->set("{$prefix}:{$aid}_prize_rank:".$id,$level,1200);
			//处理中奖率
			$rs = explode('/',$v['probability']);	
			if($rs[0]==0 || empty($v['probability'])){
				continue;
			}
			if(empty($basenum)){
				$basenum = $rs[1];
			}else{
				if(empty($basenum) || !$rs[1]){
					continue;	
				}
				$basenum = min_multiple($basenum,$rs[1]);
			}	
			$gift_base[$v['id']] =  $rs[0]/$rs[1]*$basenum;			
		}
		$redis->set("{$prefix}:{$aid}_basenum",$basenum,1200);
		$redis->set("{$prefix}:{$aid}_base_list",$gift_base,1200);
	}
	if(!$basenum){
		$basenum = 0;	
	}	
	$pid = lottery($gift_base,$basenum);
	echo 'pid:'.$pid."\n";        
	//中奖，检查数量 如数量不足 返回未中奖
	if($pid!=-1){
		// 奖品数量-1
		$now_num = $redis -> setx('incr',"{$prefix}:{$aid}_prize_num:".$pid, -1);
		echo 'now_num:'.$now_num."\n";
		if ($now_num < 0) {
			echo 'no shiwu'."\n";
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set("{$prefix}:{$aid}_prize_num:".$pid, 0);
			return -1;
		}
		$type = $redis->get("{$prefix}:{$aid}_prize_type:".$pid);
		$prize_rank = $redis->get("{$prefix}:{$aid}_prize_rank:".$pid);
		if($type != 2){
			$prizename = $redis -> get("{$prefix}:{$aid}_prize_name:".$pid);
			//减少库中实际数量
			$prize_where = array(
			'id' =>$pid,
            'aid' => $aid,
			);
			$prize_data = array(
				'num' => array('exp','`num`-1'),
				'__user_table' => 'valentine_draw_prize',
			);
			$model -> update($prize_where,$prize_data,'lottery/lottery');
			//中奖表处理
			//处理中奖表
			$now_tm= time();
			$award_data = array(
				'imsi' => $imsi,
				'award' => $prize_rank,
				'time' => $now_tm,
				'status' => 0,
				'aid' => $aid,
				'__user_table' => 'imsi_lottery_award',
			);
			$add_result = $model -> insert($award_data,'lottery/lottery');
			echo $model->getsql();
		
			/*$param =array(
				'type' => 2,
				'uid' => $uid,
				'aid' => $aid,
				'pid' => $pid,
				'username' => $username,
				'prizename' => $prizename,
				'table' => 'valentine_draw_prize',
				'table_award' => 'valentine_draw_award',
			);
			$task_client = get_task_client();
			$task_client->doBackground('recharge_top_db',json_encode($param));*/
			$resarr = array(
				'award_id' => $add_result,
				'pid' => $pid,
				'prize_rank' => $prize_rank,
				'prizename' => $prizename,
			);
			print_r($resarr);
			return json_encode($resarr);
		}
	}else{
		return -1;
	}
}

//最小公倍数    
function min_multiple($a,$b){
     $m = max($a,$b);
     $n = min($a,$b);
     for($i=1; ; $i++)
     {
         if (is_int($m*$i/$n))
         {
            return $mix=$m*$i;
         }
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
