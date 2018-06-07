<?php
/*
 *   常规充值类抽奖worker
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
$worker->addFunction("ranking_lottery", "get_award");
while ($worker->work());
function get_award($jobs){
	global $model;
	global $redis;
    $jobs= $jobs->workload();
    $jobs = json_decode($jobs,true);
    var_dump($jobs);            
    $aid = $jobs['aid'];
    $imsi = $jobs['imsi'];
    $uid = $jobs['uid'];
    $activityName= $jobs['activityName'];
    $redis->pingConn();
    $res = $redis -> get('ranking_lottery_probability:'.$aid);
	if(empty($res)){
	//if(true){//todo
		echo 'once more!';
		$nowtime = time();
		$option = array(
			'where' => array(//查询条件
				'a.activity_type_bank' => 5,//5充值消费类活动			
				'b.activate_type' => 8,
				'a.status' => 1,
				'b.status' => 1,
				'a.start_tm' => array('exp', '<='.$nowtime.''),
				'a.end_tm' => array('exp', '>='.$nowtime.''),
			),
			'table' => 'sj_activity AS a',
			'field' => 'a.id',
			'join' => array(
				'sj_activity_page AS b' => array(
						'on' => array('a.activity_page_id', 'b.ap_id')
				)
			)
		);
		$aidarr = $model->findAll($option);
		foreach($aidarr as $aid){
			$aid = $aid['id'];
			//$aid=210;
			//处理中奖率
			$nowday= strtotime(date('Y-m-d'));
			$my_option = array(
				'where' => array(//查询条件
					'a.aid' => $aid,
					'a.status' => 1,
					'b.status' => 1,
				),
				'order' => 'a.pid',
				'table' => 'gm_probability AS a',
				'field' => 'a.*',
				'join' => array(
					'gm_lottery_prize AS b' => array(
							'on' => array('a.pid', 'b.pid')
					)
				)
			);
			$my_result = $model -> findAll($my_option,'lottery/lottery');
			//echo $model->getsql();
			//取分母的最大值 作为基数
			foreach($my_result as $v){
				$redis -> set('ranking_lottery_prize_probabilityid:'.$v['pid'],$v['id'],86400);
				//处理中奖率
				$rs = explode('/',$v['probability']);
				if($rs[0]==0 || empty($v['probability'])){
					continue;
				}
				if(!isset($basenum)){
					$basenum = $rs[1];
					continue;
				}else{
                    if(empty($basenum) || !$rs[1]){
						continue;	
                    }
					$basenum = min_multiple($basenum,$rs[1]);
				}
			}
            if(!$basenum){
				$basenum = 0;	
			}
			$gift_base = array();
			foreach($my_result as $vvv){
				$rs = explode('/',$vvv['probability']);
                if(!$rs[0] || !$rs[1] || empty($vvv['probability'])){
                    continue;
                }
				$gift_base[$vvv['pid']] = $rs['0']/$rs['1']*$basenum;
			}
			$newarr = array();
			$newarr['basenum'] = $basenum;
			$newarr['gift_base'] = $gift_base;

			$rs = $redis -> set('ranking_lottery_probability:'.$aid,$newarr,1200);
			unset($basenum);

			//查询奖品类型以及当日的中奖率ID
			$my_option_prize = array(
					'where' => array(
						'aid' =>$aid,
						'status' =>1,
					),
					//'field' => 'pid,type,level,name',
					'table' => 'gm_lottery_prize'
			);
			$my_result_prize = $model -> findAll($my_option_prize,'lottery/lottery');
			foreach($my_result_prize as $vv)
			{
				$pid = $vv['pid'];
				$type = $vv['type'];
				$redis -> set('ranking_lottery_prize_type:'.$pid,$type,86400);
				$redis -> set('ranking_lottery_prize_level:'.$pid,$vv['level'],86400);
				$redis -> set('ranking_lottery_prize_name:'.$pid,$vv['name'],86400);
				if($type == 4){//礼券
					$couponId = intval($vv['gift_file']);	
					$redis->set("ranking_lottery_prize:couponId:".$pid,$couponId,1200);	
				}else if($type == 5){//直接发放的礼包
					$redis->set("ranking_lottery_prize:giftId:".$pid,json_decode($vv['gift_file'],true),1200);	
				}
			}

		   $my_option = array(
				'where' => array(//查询条件
					'a.aid' => $aid,
					'a.status' => 1,
					'b.status' => 1,
				),
				'order' => 'a.pid',
				'table' => 'gm_probability AS a',
				'field' => 'a.*',
				'join' => array(
					'gm_lottery_prize AS b' => array(
							'on' => array('a.pid', 'b.pid')
					)
				)
			);

			$my_result = $model -> findAll($my_option,'lottery/lottery');                
			foreach($my_result as $lv){
				$redis -> set('ranking_lottery_nownum:'.$aid.':'.$lv['pid'], intval($lv['now_num']));
			}
		}
	}
	$aid = $jobs['aid'];
	$res = $redis -> get('ranking_lottery_probability:'.$aid);
	//$nowtime = strtotime(date('Y-m-d'));
	echo 'ranking_lottery_probability:'.$aid;
	var_dump($res);
	$return_arr = array();
	if(empty($res)){
		return $return_arr[] = -1;
	}
	$lottery_num = $jobs['lottery_num'];
	for ($i=0; $i<$lottery_num; $i++) {
		$pid = lottery($res['gift_base'],$res['basenum']);
		echo 'pid:';
		var_dump($pid);
		
		//中奖，检查数量 如数量不足 返回未中奖
		if($pid!=-1){
			// 奖品数量-1
			$now_num = $redis -> setx('incr','ranking_lottery_nownum:'.$aid.':'.$pid, -1);
			if ($now_num < 0) {
				echo 'now_num:';
				var_dump($now_num);
				// 没有剩余奖品了，把缓存数量还原为0
				$now_num = $redis -> set('ranking_lottery_nownum:'.$aid.':'.$pid, 0);
				$return_arr[] = -1;
				continue;
			}

			//减少库中实际数量  按照中奖率ID去更新 每个奖品ID 当日对应的中奖率ID
			$vid = $redis -> get('ranking_lottery_prize_probabilityid:'.$pid);
			$where = array(
				'id' =>$vid
			);
			$data = array(
				//'now_num' => $now_num,
				'now_num' => array('exp','now_num-1'),
				'__user_table' => 'gm_probability'
			);
			$model -> update($where,$data,'lottery/lottery');

			$now_tm= time();
			$type = $redis -> get('ranking_lottery_prize_type:'.$pid);
			$level= $redis -> get('ranking_lottery_prize_level:'.$pid);
			$name = $redis -> get('ranking_lottery_prize_name:'.$pid);
			if($type==1){ //实体
				$award_data = array(
					'imsi' => $imsi,
					'level' => $level,
					'name' => '',
					'telphone' => '',
					'address' => '',
					'time' => $now_tm,
					'status' => 1,
					'aid' => $aid,
					'pid' => $pid,
					'uid' => $uid,
					'prizename' => $name,
					'__user_table' => 'gm_lottery_award'
				);
				$model -> insert($award_data,'lottery/lottery');
				var_dump($model->getsql());
			}else if($type==2){ //虚拟
				//处理  redis 的礼包
				$gift_number = json_decode($redis -> rpop("ranking_gift_{$aid}{$pid}"),true);
				if(empty($gift_number['first_text'])){
					$return_arr[] = -1;
					continue;
				}

				$where = array(
					'first_text' =>$gift_number['first_text'], //redis里来的
					'aid' =>$aid,
					'pid' =>$pid,
				);
				$data = array(
					'imsi' => $imsi,
					'status' => 1,
					'update_tm' => time(),
					'uid' => $uid,
					'__user_table' => 'gm_virtual_prize'
				);
				$model -> update($where,$data,'lottery/lottery');
				var_dump($model->getsql());
			}else if($type == 4){
					$couponId = $redis->get("ranking_lottery_prize:couponId:".$pid);	
					if(!$couponId){
						$return_arr[] = -1;
						continue;
					}				
					$ret = grant_coupon($aid,$uid,$couponId,$activityName);
					if(!$ret){
						$return_arr[] = -1;
						continue;						
					}

					$award_data = array(
							'imsi' => $imsi,
							'uid' => $uid,
							'level' => $level,
							'name' => '',
							'telphone' => '',
							'address' => '',
							'time' => $now_tm,
							'status' => 0,
							'aid' => $aid,
							'pid' => $pid,
							'prizename' => $name,
							'__user_table' => 'gm_lottery_award'
					);
					$insertid = $model -> insert($award_data,'lottery/lottery');

			}else if($type == 5){
					//礼包（直接发放）
					$giftId = get_giftid_conf($pid,$package,'ranking_lottery',$aid,$uid);         	
					if(!$giftId){
						$return_arr[] = -1;
						continue;
					}					
					$ret = grant_gift($aid,$uid,$giftId,$activityName,$appName);
					var_dump('gift_return:',$ret);
					if(!$ret){
						$return_arr[] = -1;
						continue;	
					}
					$award_data = array(
							'imsi' => $imsi,
							'uid' => $uid,
							'level' => $level,
							'name' => '',
							'telphone' => '',
							'address' => '',
							'time' => $now_tm,
							'status' => 0,
							'aid' => $aid,
							'pid' => $pid,
							'prizename' => $ret['giftSoftName'],
							'gift_code' => $ret['giftCode'],
							'__user_table' => 'gm_lottery_award'
					);
					$insertid = $model -> insert($award_data,'lottery/lottery');

					$resarr = array();
					$resarr['pid'] = $pid;
					$resarr['insertid'] = $insertid;
					$resarr['name'] = $name;
					$resarr['level'] = $level;
					$resarr['imsi'] = $imsi;
					$resarr['uid'] = $uid;
					$resarr['package'] = $ret['giftSoftPname'];
					$resarr['softname'] = $ret['giftSoftName'];
					$resarr['type'] = $type;
					$resarr['gift_number'] = $ret['giftCode'];
					print_r($resarr);
					$return_arr[] = $resarr;
					continue;
			}
			$resarr = array();
			$resarr['pid'] = $pid;
			$resarr['name'] = $name;
			$resarr['level'] = $level;
			$resarr['imsi'] = $imsi;
			$resarr['type'] = $type;
			$resarr['gift_number'] = $gift_number['first_text'];
			$resarr['softname'] = $gift_number['second_text'];
			$resarr['package'] = $gift_number['third_text'];
			var_dump($resarr);
			$return_arr[] = $resarr;
			continue;
		}
		$return_arr[] = -1;
		continue;
	}
	return json_encode($return_arr);
}

function min_multiple($a,$b)  //最小公倍数
{    
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
    $pkg_con = $redis->get("ranking_lottery_prize:giftId:".$pid);
	foreach($pkg_con as $k => $v){
		if(!$v) continue;
		foreach($v as $key => $val){
			//过滤已抽走的礼包id
			if($gift_arr){
				$res = in_array($val,$gift_arr);
				if($res) unset($pkg_con[$k][$key]);
			}
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
	writelog($log_name,"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
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
