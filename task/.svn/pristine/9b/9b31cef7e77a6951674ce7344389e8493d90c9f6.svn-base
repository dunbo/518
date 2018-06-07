<?php
/*
 *   圣诞抽奖worker
 */
include dirname(__FILE__).'/../../init.php';
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
$worker->addFunction("christmas_lottery", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);             
	$uid = $jobs['uid'];	
	$aid = $jobs['aid'];	
	$is_luxury = $jobs['is_luxury'];//1豪华2普通	
	$username= $jobs['username'];
	$package= $jobs['package'];
	if($is_luxury == 1){//豪华抽奖
		return get_luxury_lottery($aid,$uid,$username);
	}else if($is_luxury == 2){//普通抽奖
		return get_ordinary_lottery($aid,$uid,$username,$package);
	}else if($is_luxury == 3){//积分兑换奖品  
		$position = $jobs['position']; 			
		return get_integral_kind($aid,$uid,$username,$position);
	}
}
//普通抽奖
function get_ordinary_lottery($aid,$uid,$username,$package){
	global $model;
	global $redis;	
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$res = $redis->get("christmas:{$aid}_prize_name:1");
	$gift_base = $redis->get("christmas:{$aid}_base_list");
	if(empty($res) || empty($gift_base)){
		$option = array(
			'where' => array(
				'num' => array('exp','>0'),
				'aid' => $aid, //通用 活动ID
				'is_luxury' => 2,
			),
			'table' => 'integral_prize',
		);
		$prize_arr= $model->findAll($option,'lottery/lottery');
		$gift_base = array();
		foreach($prize_arr as $v){
			$id = $v['id'];
			$num = intval($v['num']);
			$name = $v['name'];
			$level = intval($v['rank']);
			$gift_base[$v['id']] = $num;
			$redis->set("christmas:{$aid}_prize_num:".$id,$num,1200);
			$redis->set("christmas:{$aid}_prize_name:".$id,$name,1200);
			$redis->set("christmas:{$aid}_prize_rank:".$id,$level,1200);
		}
		$redis->set("christmas:{$aid}_base_list",$gift_base,1200);
	}
	$pid = lottery($gift_base,10000);
	echo 'pid:'.$pid."\n";        
	//中奖，检查数量 如数量不足 返回未中奖
	if($pid!=-1){
		// 奖品数量-1
		$now_num = $redis -> setx('incr',"christmas:{$aid}_prize_num:".$pid, -1);
		echo 'now_num:'.$now_num."\n";
		if ($now_num < 0) {
			echo 'no shiwu'."\n";
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set("christmas:{$aid}_prize_num:".$pid, 0);
			$gift_number = get_gift($package,$aid);
			$param =array(
				'type' => 1,
				'uid' => $uid,
				'aid' => $aid,
				'gift_number' => json_decode($gift_number,true),
				'table_gift' => 'integral_kind_gift',
			);
			$task_client = get_task_client();
			$task_client->doBackground('recharge_top_db',json_encode($param));
			$resarr = array();
			$resarr['pid'] = 0;
			$resarr['gift_number'] = $gift_number;
			print_r($resarr);
			return json_encode($resarr);
		}
		$prizename = $redis -> get("christmas:{$aid}_prize_name:".$pid);
		$prize_rank = $redis->get("christmas:{$aid}_prize_rank:".$pid);
		//减少库中实际数量
		$param =array(
			'type' => 2,
			'uid' => $uid,
			'aid' => $aid,
			'pid' => $pid,
			'username' => $username,
			'prizename' => $prizename,
			'table' => 'integral_prize',
			'table_award' => 'integral_kind_award',
		);
		$task_client = get_task_client();
		$task_client->doBackground('recharge_top_db',json_encode($param));
		$resarr = array(
			'code' => 1,
			'pid' => $pid,
			'prize_rank' => $prize_rank,
			'prizename' => $prizename,
		);
		print_r($resarr);
		return json_encode($resarr);
	}else{
		$redis->pingConn();
		$gift_number = get_gift($package,$aid);
		$param =array(
			'type' => 1,
			'uid' => $uid,
			'aid' => $aid,
			'gift_number' => json_decode($gift_number,true),
			'table_gift' => 'integral_kind_gift',
		);		
		$task_client = get_task_client();
		$task_client->doBackground('recharge_top_db',json_encode($param));
		$resarr = array(
			'code' => 1,
			'pid' => 0,
			'gift_number' => $gift_number,
		);
		print_r($resarr);
		return json_encode($resarr);
	}	
}
//豪华抽奖
function get_luxury_lottery($aid,$uid,$username){
	global $model;
	global $redis;	
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$res = $redis->get("christmas:{$aid}_luxury_prize_name:1");
	$gift_base = $redis->get("christmas:{$aid}_luxury_base_list");
	if(empty($res) || empty($gift_base)){
		$option = array(
			'where' => array(
				'num' => array('exp','>0'),
				'aid' => $aid, //通用 活动ID
				'is_luxury' => 1,
			),
			'table' => 'integral_prize',
		);
		$prize_arr= $model->findAll($option,'lottery/lottery');
		$gift_base = array();
		foreach($prize_arr as $v){
			$id = $v['id'];
			$num = intval($v['num']);
			$name = $v['name'];
			$level = intval($v['rank']);
			$gift_base[$v['id']] = $num;
			$redis->set("christmas:{$aid}_luxury_prize_num:".$id,$num,1200);
			$redis->set("christmas:{$aid}_luxury_prize_name:".$id,$name,1200);
			$redis->set("christmas:{$aid}_luxury_prize_rank:".$id,$level,1200);
		}
		$redis->set("christmas:{$aid}_luxury_base_list",$gift_base,1200);
	}
	$pid = lottery($gift_base,3000);
	echo 'pid:'.$pid."\n";      
	//中奖，检查数量 如数量不足 返回未中奖
	if($pid!=-1){
		// 奖品数量-1
		$now_num = $redis -> setx('incr',"christmas:{$aid}_luxury_prize_num:".$pid, -1);
		echo 'now_num:'.$now_num."\n";
		if ($now_num < 0) {
			echo 'now_num:';
			var_dump($now_num);
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set("christmas:{$aid}_luxury_prize_num:".$pid, 0);
			return -1;
		}
		$prizename = $redis -> get("christmas:{$aid}_luxury_prize_name:".$pid);
		$prize_rank = $redis->get("christmas:{$aid}_luxury_prize_rank:".$pid);
		//减少库中实际数量
		$param =array(
			'type' => 2,
			'uid' => $uid,
			'aid' => $aid,
			'pid' => $pid,
			'username' => $username,
			'prizename' => $prizename,
			'table' => 'integral_prize',
			'table_award' => 'integral_kind_award',
		);
		$task_client = get_task_client();
		$task_client->doBackground('recharge_top_db',json_encode($param));
		$resarr = array(
			'code' => 1,		
			'pid' => $pid,
			'prize_rank' => $prize_rank,
			'prizename' => $prizename,
		);
		print_r($resarr);
		return json_encode($resarr);
	}
	return -1;	
}

function get_gift($package,$aid){
    global $redis;
	$redis->pingConn();	
	$pkg_key = "christmas:{$aid}_gift_pkg";
    $new_pkg = $redis->get($pkg_key);
    if(empty($package)||!in_array($package,$new_pkg)){
        $pkg_key = array_rand($new_pkg);
        $package = $new_pkg[$pkg_key];
    }
	$gift_key = "christmas:{$aid}_gift:".$package;
    //中礼包   包名 supwater
    //处理  redis 的礼包
    $gift_number = $redis -> rpop($gift_key);
    if(empty($gift_number)){
        $gift_number = $redis -> rpop($gift_key);
        if(empty($gift_number)){
            //删除 没有礼包的包名
            $new_pkg = $redis->get($pkg_key);
            foreach($new_pkg as $k=>$v){
                if($v==$package){
                    echo 'unset...pkg';
                    unset($new_pkg[$k]);
                }
            }
            $redis->set($pkg_key,$new_pkg);
            return get_gift();
        }
    }
    return $gift_number;
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
function get_integral_kind($aid,$uid,$username,$position){
	global $redis;	
	global $model;	
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();	
	$key = "christmas:{$aid}_prize:{$position}";
	$prize_info = $redis->gethash($key);		
	$now_num = $redis->setx("HINCRBY",$key,"num",-1);
	var_dump($key,$prize_info,$now_num);
	if($now_num === null || $now_num < 0){
		$redis->setx("HSET",$key,"num",0);
		$ret_arr = array(
			'code' => 0,
			'msg' => '抱歉，该奖品已被其他小伙伴先一步抢光！别气馁！已售罄的奖品，会每隔一段时间刷新新品！'
		);
		return json_encode($ret_arr);	
	}	
	$prize_info = $redis->gethash($key);
	var_dump($prize_info);	
	//减少库中实际数量
	$pid = $prize_info['pid'];
	$where = array(
		'id' => $pid,
		'aid' => $aid,
	);
	$data = array(
		'num' => array('exp','`num`-1'),
		'__user_table' => 'integral_prize'
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
		'prizename' => $prize_info['prizename'],
		'__user_table' => 'integral_kind_award'
	);
	$model -> insert($award_data,'lottery/lottery');	
	$resarr = array(
		'code' => 1,	
 		'prize_rank' => $position,
		'pid' => $pid,
		'prizename' => $prize_info['prizename'],
		'prize_integral' => $prize_info['prize_integral'],
		'pic_name' => $prize_info['pic_name'],
	);
	print_r($resarr);
	return json_encode($resarr);		
}