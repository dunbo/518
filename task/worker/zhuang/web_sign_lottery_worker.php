<?php
/*
 *   【web每日签到】抽奖worker
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
$worker->addFunction("web_sign_lottery", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);             
	$prefix = $jobs['prefix'];	
	$uid = $jobs['uid'];	
	$mid = $jobs['mid'];	
	$did = $jobs['did'];//每天签到id	
	$cid = $jobs['cid'];//连续签到id	
	$username= $jobs['username'];
	$package= $jobs['package'];
	$appName  = $jobs['appName'];	
	$activityName= $jobs['activityName'];
	$imei = $jobs['imei'];
	$ip = $jobs['ip'];
	$email = $jobs['email'];
	$mobile = $jobs['mobile'];
	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	if($did > 0){
		$base_key = "{$prefix}:{$mid}:did:{$did}:base_list";
		$basenum_key = "{$prefix}:{$mid}:did:{$did}:basenum";
		$prize_type_key = "{$prefix}:{$mid}:did:{$did}:prize_type:";
		$prize_num_key = "{$prefix}:{$mid}:did:{$did}:prize_num:";
		$prize_name_key = "{$prefix}:{$mid}:did:{$did}:prize_name:";
		$prize_rank_key = "{$prefix}:{$mid}:did:{$did}:prize_rank:";
		$couponId_key = "{$prefix}:{$mid}:did:{$did}:couponId:";
		$giftId_key = "{$prefix}:{$mid}:did:{$did}:giftId:";
	}else{
		$base_key = "{$prefix}:{$mid}:cid:{$cid}:base_list";
		$basenum_key = "{$prefix}:{$mid}:cid:{$cid}:basenum";		
		$prize_type_key = "{$prefix}:{$mid}:cid:{$cid}:prize_type:";
		$prize_num_key = "{$prefix}:{$mid}:cid:{$cid}:prize_num:";
		$prize_name_key = "{$prefix}:{$mid}:cid:{$cid}:prize_name:";
		$prize_rank_key = "{$prefix}:{$mid}:cid:{$cid}:prize_rank:";
		$couponId_key = "{$prefix}:{$mid}:cid:{$cid}:couponId:";
		$giftId_key = "{$prefix}:{$mid}:cid:{$cid}:giftId:";
	}
	$gift_base = $redis->get($base_key);
	$basenum = $redis->get($basenum_key);
	if(empty($basenum) || empty($gift_base)){
		$where = array(
			'num' => array('exp','>0'),
			'probability' => array('exp','!=0'),
			'mid' => $mid, //月份id
			'status' => 1,			
		);
		if($did > 0){
			$where['did'] = $did;
		}else{
			$where['cid'] = $cid;
		}
		$option = array(
			'where' => $where,
			'table' => 'qd_draw_prize',
		);
		$prize_arr= $model->findAll($option,'lottery/lottery');
		$gift_base = array();
		foreach($prize_arr as $v){
			$id = $v['id'];
			$num = intval($v['num']);
			$name = $v['name'];
			$level = intval($v['level']);
			$type = intval($v['type']);	
			$redis->set($prize_type_key.$id,$type,1200);
			$redis->set($prize_num_key.$id,$num,1200);
			$redis->set($prize_name_key.$id,$name,1200);
			$redis->set($prize_rank_key.$id,$level,1200);
			$couponId = intval($v['gift_file']);	
			if($type == 3){
				$redis->set($couponId_key.$id,$couponId,1200);	
			}else if($type == 5){
				$redis->set($giftId_key.$id,json_decode($v['gift_file'],true),1200);	
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
	$pid = lottery($gift_base,$basenum);
	echo 'pid:'.$pid."\n";        
	//中奖，检查数量 如数量不足 返回未中奖
	if($pid!=-1){
		// 奖品数量-1
		$now_num = $redis -> setx('incr',$prize_num_key.$pid, -1);
		echo 'now_num:'.$now_num."\n";
		if ($now_num < 0) {
			echo 'no shiwu'."\n";
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set($prize_num_key.$pid, 0);
			return -1;
		}
		$type = $redis->get($prize_type_key.$pid);
		$prize_rank = $redis->get($prize_rank_key.$pid);
		$prizename = $redis -> get($prize_name_key.$pid);
		if($type != 4){
			if($type == 6){				
				save_prize_num($mid,$pid);	
				return -1;
			}
			if($type == 3){
				$couponId = $redis->get($couponId_key.$pid);	
				if(!$couponId){
					$resarr = array(
						'code' => 0,
						'pid' => $pid,
						'prize_rank' => $prize_rank,
						'prizename' => $prizename,
						'msg' => "礼券id无效",
					);
					return json_encode($resarr);
				}				
				$ret = grant_coupon($mid,$uid,$couponId,$activityName);
				if(!$ret){
					$resarr = array(
						'code' => 0,
						'pid' => $pid,
						'prize_rank' => $prize_rank,
						'prizename' => $prizename,
						'msg' => "礼券发放失败",
					);
					return json_encode($resarr);
				}
			}else if($type == 5){
				//礼包（直接发放）
				$giftId = get_giftid_conf($pid,$package,$prefix,$mid,$uid,$did,$cid);
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
				$ret = grant_gift($mid,$uid,$giftId,$activityName,$appName);
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
		}else{
			$gift_number = get_gift($package,$mid,$pid);
			if(!$gift_number){
				return -1;
			}			
		}
		//减少库中实际数量
		save_prize_num($mid,$pid);
		//奖品记录
		//$prizename = $type == 5 ? $ret['giftSoftName']: $prizename;
		$data = array(
			'mid' => $mid,
			'did' => $did,
			'cid' => $cid,
			'pid' => $pid,
			'uid' => $uid,
			'username' => $username,
			'prizename' => $prizename,
			'type' => $type,
			'ip' => $ip,
			'imei' => $imei,
			'gift_number' => $gift_number,
			'email' => $email,
			'mobile' => $mobile,
			'ext' => $type == 3 || $type == 5 ? $ret : '',
		);
		$award_id = add_award($data);
		$resarr = array(
			'code' => 1,
			'pid' => $pid,
			'award_id' => $award_id,
			'type' => $type,
			'prize_rank' => $prize_rank,
			'prizename' => 	$prizename,
			'package' => $type == 5 ? $ret['giftSoftPname'] : '',
			'softname' => $type == 5 ? $ret['giftSoftName'] : '',
			'gift_number' => $type == 5 ? $ret['giftCode'] : '',
		);
		if($type == 4){
			$resarr['gift_number'] = $gift_number;
		}
		print_r($resarr);
		return json_encode($resarr);		
	}else{
		return -1;
	}
}
//奖品数减1
function save_prize_num($mid,$pid){
	global $model;	
	$where = array(
		'id' =>$pid,
		'mid' => $mid,
	);
	$data = array(
		'num' => array('exp','`num`-1'),
		'__user_table' => 'qd_draw_prize',
	);
	$model -> update($where,$data,'lottery/lottery');		
}
//记录中奖信息
function add_award($data){
	global $model;	
	$now_tm= time();
	if($data['type'] == 4){
		$giftnumber = json_decode($data['gift_number'],true);
		$where = array(
			'gift_number' =>$giftnumber['gift_number'],
			'mid' => $data['mid'],
			'pid'=>$data['pid']
		);
		$option = array(
			'where' => $where,
			'table' => 'qd_sign_gift',
		);
		$list = $model->findOne($option,'lottery/lottery');
		if($list){
			$map = array(
				'uid' => $data['uid'],
				'status' => 1,
				'update_tm' => $now_tm,
				'__user_table' => 'qd_sign_gift'
			);
			$model -> update($where,$map,'lottery/lottery');
		}
		$new_ext_arr = array(
			'package' => $list['package'],
			'softname' => $list['softname'],
			'giftCode' => $list['gift_number'],
		);	
		//使用时间
		$virtual_start_tm	 = $list['start_tm'];
		$virtual_end_tm	 = $list['end_tm'];	
	}else{
		$ext_arr = json_decode($data['ext']['data'],true);
		if($data['type'] == 5){
			//直接发放礼包
			$new_ext_arr = array(
				'package' => $data['ext']['giftSoftPname'],
				'softname' => $data['ext']['giftSoftName'],
				'giftCode' => $data['ext']['giftCode'],
				'giftName' => $data['ext']['giftName'],
				'instructions' => $ext_arr['useWay'],//使用方法
				'application' => $ext_arr['useRange'],//适用范围
				'remark' => $ext_arr['remark'],//礼包详情
			);
			//使用时间
			$virtual_start_tm	 = strtotime($ext_arr['strPrSdate']);
			$virtual_end_tm	 = strtotime($ext_arr['strPrEdate']);
		}else if($data['type'] == 3){
			//直接发放礼券
			$new_ext_arr = array(
				'money' => $ext_arr['couponAmount'],//礼券金额
				'instructions' => $ext_arr['memo'],//使用条件
				'application' => $ext_arr['appDomainRemark'],//适用范围
			);
			//使用时间
			$virtual_start_tm	 = intval($ext_arr['validStartTime']/1000);
			$virtual_end_tm	 = intval($ext_arr['validEndTime']/1000);			
		}
	}	
	$award_data = array(
		'uid' => $data['uid'],
		'mid' => $data['mid'],
		'username' => $data['username'],
		'create_tm' => $now_tm,
		'status' => 1,
		'pid' => $data['pid'],
		'did' => $data['did'],
		'cid' => $data['cid'],
		'prizename' => $data['prizename'],
		'type' => $data['type'],
		'imei'=>$data['imei'],
		'ip'=>$data['ip'],
		'email'=>$data['email'],
		'mobile'=>$data['mobile'],
		'ext'=>json_encode($new_ext_arr),
		'is_pub' => in_array($data['type'],array(3,4,5)) ? 1 : 0,
		'pub_tm' => in_array($data['type'],array(3,4,5))? $now_tm : 0,
		'virtual_start_tm' => in_array($data['type'],array(3,4,5)) ? $virtual_start_tm : 0,
		'virtual_end_tm' => in_array($data['type'],array(3,4,5)) ? $virtual_end_tm : 0,
		'__user_table' => 'qd_draw_award'
	);
	$award_id = $model -> insert($award_data,'lottery/lottery');	
	return $award_id;
}
function get_gift($package,$aid,$pid){
    global $redis;
	$prefix = "web_sign_gift";
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
function get_giftid_conf($pid,$package,$prefix,$mid,$uid,$did,$cid){
    global $redis;	
	global $model;
	if($did > 0){
		$giftId_key = "{$prefix}:{$mid}:did:{$did}:giftId:";
		$gift_user_key = "{$prefix}:{$mid}:did:{$did}:gift_user:".$uid;
	}else{
		$giftId_key = "{$prefix}:{$mid}:cid:{$cid}:giftId:";
		$gift_user_key = "{$prefix}:{$mid}:cid:{$cid}:gift_user:".$uid;
	}	
	$gift_arr = $redis->get($gift_user_key);	
	//var_dump($gift_arr);
	$pkg_con = $redis->get($giftId_key.$pid);
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
		if($gift_arr){
			$arr_con = array_merge($gift_arr,array($gift_id));
		}else{
			$arr_con = array($gift_id);
		}
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
	$path = "/data/att/permanent_log/web_sign_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}