<?php
/*
 *   活动抽奖worker
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
$worker->addFunction("activity_lottery", "get_award");
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
	$appName  = $jobs['appName'];	
	$activityName= $jobs['activityName'];
	$table = $jobs['table'];
	$gift_table = $jobs['gift_table'];
	$table_award = $jobs['table_award'];
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$base_key = "{$prefix}:{$aid}:base_list";
	$basenum_key = "{$prefix}:{$aid}:basenum";
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
			'table' => $table,
		);
		$prize_arr= $model->findAll($option,'lottery/lottery');
		$gift_base = array();
		foreach($prize_arr as $v){
			$id = $v['id'];
			$num = intval($v['num']);
			$name = $v['name'];
			$level = intval($v['level']);
			$type = intval($v['type']);	
			
			$redis->set("{$prefix}:{$aid}:prize_type:".$id,$type,1200);
			$redis->set("{$prefix}:{$aid}:prize_num:".$id,$num,1200);
			$redis->set("{$prefix}:{$aid}:prize_name:".$id,$name,1200);
			$redis->set("{$prefix}:{$aid}:prize_rank:".$id,$level,1200);
			if($type == 4){
				$couponId = intval($v['gift_file']);	
				$redis->set("{$prefix}:{$aid}:couponId:".$id,$couponId,1200);	
			}else if($type == 5){
				$redis->set("{$prefix}:{$aid}:giftId:".$id,json_decode($v['gift_file'],true),1200);	
			}
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
			//$gift_base[$v['id']] =  $rs[0]/$rs[1]*$basenum;			
		}
		if(!$basenum) $basenum = 0;			
		foreach($prize_arr as $vvv){
			$rs = explode('/',$vvv['probability']);
			if(!$rs[0] || !$rs[1] || empty($vvv['probability'])){
				continue;
			}			
			$gift_base[$vvv['id']] = $rs['0']/$rs['1']*$basenum;
		}		
		$redis->set($basenum_key,$basenum,1200);
		$redis->set($base_key,$gift_base,1200);
	}
	//if(!$basenum)	$basenum = 0;		
	$pid = lottery($gift_base,$basenum);
	echo 'pid:'.$pid."\n";        
	//中奖，检查数量 如数量不足 返回未中奖
	if($pid!=-1){
		// 奖品数量-1
		$now_num = $redis -> setx('incr',"{$prefix}:{$aid}:prize_num:".$pid, -1);
		echo 'now_num:'.$now_num."\n";
		if ($now_num < 0) {
			echo 'no shiwu'."\n";
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set("{$prefix}:{$aid}:prize_num:".$pid, 0);
			return -1;
		}
		$type = $redis->get("{$prefix}:{$aid}:prize_type:".$pid);
		$prize_rank = $redis->get("{$prefix}:{$aid}:prize_rank:".$pid);
		if($type != 2){
			if($type == 3){				
				$where = array(
					'id' =>$pid,
					'aid' => $aid,
				);
				$data = array(
					'num' => array('exp','`num`-1'),
					'__user_table' => $table,
				);
				$model -> update($where,$data,'lottery/lottery');		
				return -1;
			}
			$prizename = $redis -> get("{$prefix}:{$aid}:prize_name:".$pid);
			if($type == 4){
				$couponId = $redis->get("{$prefix}:{$aid}:couponId:".$pid);	
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
			}else if($type == 5){
				//礼包（直接发放）
				$giftId = get_giftid_conf($pid,$package,$prefix,$aid,$uid);
				if(!$giftId){
					$resarr = array(
						'code' => 0,
						'pid' => $pid,
						'prize_rank' => $prize_rank,
						'prizename' => $prizename,
						'msg' => "礼包id无效",
					);
					return json_encode($resarr);
				}					
				$ret = grant_gift($aid,$uid,$giftId,$activityName,$appName);
				if(!$ret){
					$resarr = array(
						'code' => 0,
						'pid' => $pid,
						'prize_rank' => $prize_rank,
						'prizename' => $prizename,
						'msg' => "礼包发放失败",
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
				//@
				'prizename' => $type == 5 ? $ret['giftSoftName'].":".$ret['giftCode'] : $prizename,
				'table' => $table,
				'table_award' => $table_award,
			);
			$task_client = get_task_client();
			$task_client->doBackground('recharge_top_db',json_encode($param));
			$resarr = array(
				'code' => 1,
				'pid' => $pid,
				'type' => $type,
				'prize_rank' => $prize_rank,
				'prizename' => 	$prizename,
				'package' => $type == 5 ? $ret['giftSoftPname'] : '',
				'softname' => $type == 5 ? $ret['giftSoftName'] : '',
				'gift_number' => $type == 5 ? $ret['giftCode'] : '',
			);
			print_r($resarr);
			return json_encode($resarr);
		}else{
			return lottery_gift($package,$aid,$pid,$prize_rank,$uid,$table,$gift_table);
		}
	}else{
		return -1;
	}
}
function lottery_gift($package,$aid,$pid,$prize_rank,$uid,$table,$gift_table){
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
		'__user_table' => $table,
	);
	$model -> update($where,$data,'lottery/lottery');	
	$gift = json_decode($gift_number,true);	
	$where = array(
		'gift_number' => $gift['gift_number'],
		'aid' => $aid,
		'pid' => $pid,
	);
	$data = array(
		'uid' => $uid,
		'aid' => $aid,
		'status' => 1,
		'update_tm' => time(),
		'__user_table' => $gift_table
	);
	$model -> update($where,$data,'lottery/lottery');	
	$resarr = array(
		'code' => 1,
		'pid'  =>$pid,
		'type' => 2,
		'prize_rank' => $prize_rank,
		'gift_number' => $gift_number,
	);
	print_r($resarr);
	return json_encode($resarr);		
}
function get_gift($package,$aid,$pid){
    global $redis;
	$prefix = "activity_gift";
	$redis->pingConn();	
	$pkg_key = "{$prefix}:{$aid}:{$pid}:pkg";
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
	$gift_key = "{$prefix}:{$aid}:{$pid}:gift_num:{$package}";
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
	$res = get_data($data,'coupon');
	if($res['code'] != '200'){
		return false;
	}else{
		return $res;
	}
}
//获取礼包配置id
//a.优先抽中已安装游戏的虚拟礼包id；
//b.同一用户不重复中同一款礼包id；
//c.若用户抽奖的次数，超过了虚拟礼包的种类数量，则随机中虚拟礼包id。
function get_giftid_conf($pid,$package,$prefix,$aid,$uid){
    global $redis;	
	global $model;
	$gift_user_key = "{$prefix}:{$aid}:gift_user:".$uid;
	$gift_arr = $redis->get($gift_user_key);	
	//var_dump($gift_arr);
	$pkg_con = $redis->get("{$prefix}:{$aid}:giftId:".$pid);
	foreach($pkg_con as $k => $v){
		if(!$v) continue;
		foreach($v as $key => $val){
			//过滤已抽走的礼包id
			$res = in_array($val,$gift_arr);
			if($res) unset($pkg_con[$k][$key]);
		}
		if(empty($pkg_con[$k])){
			unset($pkg_con[$k]);
		}else{	
			sort($pkg_con[$k]);
		}
		if(count($pkg_con[$k]) > 0){
			$pkg_key = $k;
		}
	}
	if(!$pkg_key){
		$redis->delete($gift_user_key);
		$gift_arr = $redis->get($gift_user_key);	
	}
	//优先发放已安装
	foreach(explode(";",$package) as $v){
		if($pkg_con[$v][0]){
			$gift_id = $pkg_con[$v][0];
			$res = in_array($gift_id,$gift_arr);
			if(!$res){
				if($gift_arr && $gift_id){
					$arr_con = array_merge($gift_arr,array($gift_id));
				}else{
					$arr_con = array($gift_id);
				}
				$redis -> set($gift_user_key,$arr_con,10*86400);
			}
			break;
		}
	}
	//随机发放
	if(!$gift_id){
		$gift_id = $pkg_con[$pkg_key][0];
		$arr_con = array_merge($gift_arr,array($gift_id));
		$redis -> set($gift_user_key,$arr_con,10*86400);
		//var_dump($pkg_key);
	}
	return $gift_id;
}


//发放礼包
function grant_gift($aid,$uid,$giftId,$activityName,$appName){
	$data_arr = array(
		'uid'			=>	$uid,
		'giftId'		=>	$giftId,
		'exchangeTime'	=>	time(),
		'exchangeNum'	=>	1,
		'appName'		=>	$appName,
		'activityName'	=>	$activityName,
	);	
	$js_data = json_encode($data_arr);
	$data = array(
		'serviceId' => '',
		'data' => $js_data
	);
	$res = get_data($data,'gift');
	if($res['code'] != '200'){
		return false;
	}else{
		return $res;
	}	
}
function get_data($vals,$type){
	$host = load_config('pay_host');
	if( $type =="gift" ) {
		//礼包
		$url	=	$host.'/pay/internal/gift/exchange';
	}else {
		//礼券
		$url   =   $host.'/pay/internal/coupon/exchange';//测试地址
	}		
    $vals = http_build_query($vals);
    $res = httpGetInfo($url,$vals,$type);
    $last = json_decode($res,true);
    //var_dump($last);
    return $last;
}

function httpGetInfo($url, $vals,$type) {
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
	if( $type =="gift" ) {
		$log_name = "gift_http.log";
	}else{
		$log_name = "coupon_http.log";
	}
	writelog($log_name,"{$http_code}|{$errno}|{$error}\n" .$url."\n". print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
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