<?php
/*
 *   问道类抽奖worker
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
$worker->addFunction("recharge_lottery", "get_award");
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
	$username= $jobs['username'];
	$package= $jobs['package'];
	$money= $jobs['money'];
	$activityName= $jobs['activityName'];
	if($money >= 6 and $money < 198){
		$probability_type = 1;
	}else if($money >= 198 and $money < 648){
		$probability_type = 2;
	}else{
		$probability_type = 3;
	}
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$base_key = "{$prefix}:{$aid}:base_list:".$probability_type;
	$basenum_key = "{$prefix}:{$aid}:basenum:".$probability_type;
	$gift_base = $redis->get($base_key);
	$basenum = $redis->get($basenum_key);
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
			
			$redis->set("{$prefix}:{$aid}:prize_type:".$id.":".$probability_type,$type,1200);
			$redis->set("{$prefix}:{$aid}:prize_num:".$id.":".$probability_type,$num,1200);
			$redis->set("{$prefix}:{$aid}:prize_name:".$id.":".$probability_type,$name,1200);
			$redis->set("{$prefix}:{$aid}:prize_rank:".$id.":".$probability_type,$level,1200);
			if($type == 4){
				$couponId = intval($v['gift_file']);	
				$redis->set("{$prefix}:{$aid}:couponId:".$id.":".$probability_type,$couponId,1200);	
			}
			//处理中奖率
			list($probability,$probability1,$probability2) = explode(';',$v['probability']);	
			if($probability_type == 1){
				if($probability == 0 ) continue;
				$rs = explode('/',$probability);	
			}else if($probability_type == 2){
				if($probability1 == 0 ) continue;
				$rs = explode('/',$probability1);	
			}else{
				if($probability2 == 0 ) continue;
				$rs = explode('/',$probability2);	
			}
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
		$redis->set($basenum_key,$basenum,1200);
		$redis->set($base_key,$gift_base,1200);
	}
	if(!$basenum){
		$basenum = 0;	
	}	
	$pid = lottery($gift_base,$basenum);
	echo 'pid:'.$pid."\n";        
	//中奖，检查数量 如数量不足 返回未中奖
	if($pid!=-1){
		// 奖品数量-1
		$now_num = $redis -> setx('incr',"{$prefix}:{$aid}:prize_num:".$pid.":".$probability_type, -1);
		echo 'now_num:'.$now_num."\n";
		if ($now_num < 0) {
			echo 'no shiwu'."\n";
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set("{$prefix}:{$aid}:prize_num:".$pid.":".$probability_type, 0);
			return -1;
		}
		$type = $redis->get("{$prefix}:{$aid}:prize_type:".$pid.":".$probability_type);
		$prize_rank = $redis->get("{$prefix}:{$aid}:prize_rank:".$pid.":".$probability_type);
		if($type != 2){
			$prizename = $redis -> get("{$prefix}:{$aid}:prize_name:".$pid.":".$probability_type);
			if($type == 4){
				$couponId = $redis->get("{$prefix}:{$aid}:couponId:".$pid.":".$probability_type);	
				if(!$couponId){
					$resarr = array(
						'code' => 0,
						'pid' => $pid,
						'prize_rank' => $prize_rank,
						'prizename' => $prizename,
						'msg' => "礼卷id无效",
					);
					return json_encode($resarr);
				}				
				$ret = grant_coupon($aid,$uid,$couponId,$activityName);
				if(!$ret){
					$resarr = array(
						'code' => 0,
						'pid' => $pid,
						'prize_rank' => $prize_rank,
						'prizename' => $prizename,
						'msg' => "礼卷发放失败",
					);
					return json_encode($resarr);
				}
			}
			//减少库中实际数量
			$param =array(
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
			$task_client->doBackground('recharge_top_db',json_encode($param));
			$resarr = array(
				'pid' => $pid,
				'prize_rank' => $prize_rank,
				'prizename' => $prizename,
			);
			print_r($resarr);
			return json_encode($resarr);
		}else{
			return lottery_gift($package,$aid,$pid,$prize_rank,$uid);
		}
	}else{
		return -1;
	}
}
function lottery_gift($package,$aid,$pid,$prize_rank,$uid){
	global $model;
	$gift_number = get_gift($package,$aid,$pid);
	if(!$gift_number){
		return -1;
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
		'gift_number' => json_decode($gift_number,true),
		'aid' => $aid,
		'pid' => $pid,
	);
	$data = array(
		'uid' => $uid,
		'aid' => $aid,
		'status' => 1,
		'update_tm' => time(),
		'__user_table' => 'valentine_draw_gift'
	);
	$model -> update($where,$data,'lottery/lottery');	
	$resarr = array();
	$resarr['pid'] =$pid;
	$resarr['prize_rank'] = $prize_rank;
	$resarr['gift_number'] = $gift_number;
	print_r($resarr);
	return json_encode($resarr);		
}
function get_gift($package,$aid,$pid){
    global $redis;
	$prefix = "custom";
	$redis->pingConn();	
	$pkg_key = "{$prefix}:{$aid}:{$pid}_gift_pkg";
    $new_pkg = $redis->get($pkg_key);
	if(!$new_pkg){
		return false;
	}
    if(empty($package)||!in_array($package,$new_pkg)){
        $pkg_key = array_rand($new_pkg);
        $package = $new_pkg[$pkg_key];
    }

    //中礼包   包名 supwater
    //处理  redis 的礼包
	$gift_key = "{$prefix}:{$aid}:{$pid}_gift:{$package}";
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
//发放礼券
function grant_coupon($aid,$uid,$couponId,$activityName){		
	$data_arr =  array(
		'uid' => $uid,
		'couponId' => $couponId,
		'exchangeTime' =>time(),
		'exchangeNum' => 1,
		'activityName' => $activityName,
		'activityId' => $aid,
	);
	$js_data = json_encode($data_arr);
	$data = array(
		'serviceId' => '',
		'data' => $js_data
	);
	$res = get_data($data);
	if($res['code'] != '200'){
		return false;
	}else{
		return true;
	}
}

function get_data($vals){
	$ip = getserverip();
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' || $ip == "192.168.1.242"){
		$url = 'http://dev2.user.anzhi.com:9021/pay/internal/coupon/exchange';//测试地址		
	}else{
		$url = 'http://pay.anzhi.com/pay/internal/coupon/exchange';//线上地址
	}	
    $vals = http_build_query($vals);
    $res = httpGetInfo($url,$vals);
    $last = json_decode($res,true);
    //var_dump($last);
    return $last;
}

function httpGetInfo($url, $vals) {
	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);
	writelog('coupon_http.log',"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	return $result;
}

//日志
function writelog($filename,$msg){
	$now = time();
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}