<?php
/*
 *   6.5红包活动抽奖worker
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
$worker->addFunction("red_package_worker", "get_award");
while ($worker->work());

function get_award($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);             
        $session = $jobs['session'];
        $prefix = 'red_package';
        $imsi = $jobs['imsi'];
        $imei = $jobs['imei'];
        $mac = $jobs['mac'];
	$uid = $jobs['uid'];
	$aid = $jobs['aid'];
        writelog('red_package_'.$aid.'.log',json_encode($jobs));
        if(empty($uid)){
            return -1;
        }
	$red_id = $jobs['red_id'];//叠叠乐用

        //如果活动结束了 直接-1
        $option = array(
                'where' => array(
                        'id' => $aid
                ),
                'table' => 'sj_activity',
        );  

        $activity = $model->findOne($option);

        if(time()>$activity['end_tm']||$activity['status']!=1){
            return -1;
        }


	//如果缓存没了 重新生成
	//剩余奖品数量缓存
	$redis->pingConn();
	$base_key = "{$prefix}:{$aid}:base_list";

        if(!empty($red_id)){
	    $basenum_key = "{$prefix}:{$aid}:{$red_id}:basenum";
        }else{
	    $basenum_key = "{$prefix}:{$aid}:basenum";
        }

	$gift_base = $redis->get($base_key);
	$basenum = $redis->get($basenum_key);
	if(empty($basenum) || empty($gift_base)){
                if(!empty($red_id)){//叠叠乐
                    $option = array(
                            'where' => array(
                                    'num' => array('exp','>0'),
                                    'probability' => array('exp','!=0'),//中奖概率
                                    'aid' => $aid,
                                    'red_id' => $red_id,
                                    'status' => 1,
                            ),
                            'table' => 'sign_prize',
                    );
                }else{
                        $option = array(
                            'where' => array(
                                    'num' => array('exp','>0'),
                                    'probability' => array('exp','!=0'),//中奖概率
                                    'aid' => $aid,
                                    'status' => 1,
                            ),
                            'table' => 'sign_prize',
                    );  
                }

		$prize_arr= $model->findAll($option,'lottery/lottery');
		$gift_base = array();
		foreach($prize_arr as $v){
			$id = $v['id'];
			$num = intval($v['num']);
			$name = $v['name'];
			$task_id = $v['task_id'];
			$level = intval($v['level']);
			$type = intval($v['type']);	
                        if(!empty($red_id)){
			    $red_package_id = intval($v['d_redid']);	
                        }else{
			    $red_package_id = intval($v['red_id']);	
			    $package= $v['package'];
                        }
			$red_package_id_d = intval($v['d_redid']);	
			
			$redis->set("{$prefix}:{$aid}:red_package_task_type:".$id,$v['task_type'],1200);
			$redis->set("{$prefix}:{$aid}:red_package_task_id:".$id,$task_id,1200);
			$redis->set("{$prefix}:{$aid}:prize_package:".$id,$package,1200);
			$redis->set("{$prefix}:{$aid}:prize_type:".$id,$type,1200);
			$redis->set("{$prefix}:{$aid}:prize_num:".$id,$num,1200);
			$redis->set("{$prefix}:{$aid}:prize_name:".$id,$name,1200);
			$redis->set("{$prefix}:{$aid}:prize_level:".$id,$level,1200);
			$redis->set("{$prefix}:{$aid}:red_package_id:".$id,$red_package_id,1200);
			$redis->set("{$prefix}:{$aid}:red_package_id_d:".$id,$red_package_id_d,1200);


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
                }

		foreach($prize_arr as $v){
			$rs = explode('/',$v['probability']);	
                        writelog('red_package_'.$aid.'.log','jisuan:'.json_encode($rs).json_encode($basenum));
			$gift_base[$v['id']] =  $rs[0]/$rs[1]*$basenum;			
                }
		$redis->set($basenum_key,$basenum,1200);
		$redis->set($base_key,$gift_base,1200);
	}
	if(!$basenum){
		$basenum = 0;
	}


	var_dump($gift_base,$basenum);
        writelog('red_package_'.$aid.'.log',json_encode($gift_base).json_encode($basenum));
	$pid = lottery($gift_base,$basenum);
	echo 'pid:'.$pid."\n";        
	//中奖，检查数量 如数量不足 返回未中奖
        if($pid!=-1){

		$task_type = $redis->get("{$prefix}:{$aid}:red_package_task_type:".$pid);
		$type = $redis->get("{$prefix}:{$aid}:prize_type:".$pid);
		$level = $redis->get("{$prefix}:{$aid}:prize_level:".$pid); //level
		$red_package_id = $redis->get("{$prefix}:{$aid}:red_package_id:".$pid); // 红包ID
		$task_id = $redis->get("{$prefix}:{$aid}:red_package_task_id:".$pid); //任务ID
		$red_package_id_d= $redis->get("{$prefix}:{$aid}:red_package_id_d:".$pid); //叠叠乐的
		$package = $redis->get("{$prefix}:{$aid}:prize_package:".$pid);

                //验证设备是否领取过了
		$option = array(
			//'table' => 'sj_redpacket_bill',
			'table' => 'sj_redpacket_pre_bill',
			'where' => array(
				'packid' => $red_package_id,
				'imei' => array('exp',"='{$imei}' or mac = '{$mac}'"),
			),
		);
		
		$ress = $model -> findOne($option,'redpk_s/redpacket');
                if($ress){
                    writelog('red_package_'.$aid.'.log','设备或mac已预拆:'.$model->getsql());
                    return -1;
                }

		$option = array(
			'table' => 'sj_redpacket_bill',
			'where' => array(
				'packid' => $red_package_id,
				'imei' => array('exp',"='{$imei}' or mac = '{$mac}'"),
			),
		);
		
		$resst = $model -> findOne($option,'redpk_s/redpacket');
                if($resst){
                    writelog('red_package_'.$aid.'.log','设备或mac已领取:'.$model->getsql());
                    return -1;
                }


                //检查用户是否已经抽过该包名的红包  只针对任务红包
                /*
                if(!empty($package)){
                    $option = array(
                            'where' => array(
                                    'uid' => $uid,
                                    //'aid' => $aid, //todo
                                    //'status' => 2,
                                    'package' => $package,
                            ),
                            'table' => 'red_package_award',
                    );

                    $is_award = $model->findOne($option,'lottery/lottery');
                    if($is_award){
                        writelog('red_package_'.$aid.'.log',$package.'包名已做过'.$model->getsql());
                        return -1;
                    }
                }*/

                //检查用户是否已经抽过该红包ID，
                $option = array(
                        'where' => array(
                                'uid' => $uid,
                                //'status' => 2,
                                'red_id' => $red_package_id,
                        ),
                        'table' => 'red_package_award',
                );  

		$is_award = $model->findOne($option,'lottery/lottery');
                if($is_award){
                    writelog('red_package_'.$aid.'.log','中过了'.$model->getsql());
                    return -1;
                }else{
                    writelog('red_package_'.$aid.'.log','没中过'.$model->getsql());
                }

                //任务是否做过 过滤 调用JAVA接口 叠叠乐叠加不需要
                if(empty($red_id)){//非叠叠乐
                    if(!empty($session)&&!empty($package)){
                        $is_do = task_ishasdone($task_type,$package,$session,$aid);
                        if($is_do===true){
                            writelog('red_package_'.$aid.'.log','该任务在java那边做过'.json_encode($is_do));
                            return -1;
                        }else if($is_do!==false){
                            writelog('red_package_'.$aid.'.log','接口异常情况，如9016,999'.json_encode($is_do));
                            return -1;
                        }
                    }
                }

		// 奖品数量-1
		$now_num = $redis -> setx('incr',"{$prefix}:{$aid}:prize_num:".$pid, -1);
		echo 'now_num:'.$now_num."\n";
                writelog('red_package_'.$aid.'.log','pid is '.$pid.',num is '.$now_num);
		if ($now_num < 0) {
			echo 'no shiwu'."\n";
			// 没有剩余奖品了，把缓存数量还原为0
			$now_num = $redis -> set("{$prefix}:{$aid}:prize_num:".$pid, 0);
                        writelog('red_package_'.$aid.'.log','no prize,return -1');
			return -1;
		}



                //减少实际数量
				$where = array(
					'id' =>$pid,
					'aid' => $aid,
				);
				$data = array(
					'num' => array('exp','`num`-1'),
					'__user_table' => 'sign_prize',
				);
				$model -> update($where,$data,'lottery/lottery');		

			        $prizename = $redis -> get("{$prefix}:{$aid}:prize_name:".$pid);
			
                                if(!empty($red_id)){//叠叠乐
                                    $new_red_package_id=$red_package_id_d;

                                }else{
                                    $new_red_package_id=$red_package_id;
                                }

                                $award_data = array(
                                        'imsi' => $imsi,
                                        'imei' => $imei,
                                        'mac' => $mac,
                                        'uid' => $uid,
                                        'level' => $level,
                                        'time' => time(),
                                        'status' => 0,
                                        'aid' => $aid,
                                        'package' => $package,
                                        'red_id' => $new_red_package_id,
                                        'pid' => $pid,
                                        'prizename' => $prizename,
                                        'activity_type' => $activity['activity_type'],
                                        '__user_table' => 'red_package_award'
                                );
                                $insertid = $model -> insert($award_data,'lottery/lottery');


                        $key = "red_package:".$aid.":uid:".$uid.":red_package_list";//未领取红包列表

                        $arr[$insertid] = array(
                            'red_id'=>$red_package_id,
                            'package'=>$package,
                            'task_id'=>$task_id,
                            'status'=>0,
                            'time'=>time()
                        );


                        $redis->sethash($key,$arr,86400*90);

			$resarr = array(
				'pid' => $pid,
				'level' => $level,
				'red_package_id' => $red_package_id,
				'red_package_id_d' => $red_package_id_d,
				'task_id' => $task_id,
				'package' => $package,
				'prizename' => 	$prizename,
				'insertid' => 	$insertid,
			);
			print_r($resarr);
                        writelog('red_package_'.$aid.'.log',json_encode($resarr));
			return json_encode($resarr);
	}else{
                writelog('red_package_'.$aid.'.log','return -1');
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

//验证是否做过该包名的任务
function task_ishasdone($tasktype,$pkg,$session,$aid){
        $pid  = $session['USER_ID'];
	global $redis;

	$task_ishasdone_key = "task_ishasdone:".$pid.":".$pkg.":".$tasktype;
	$ishasdone = $redis->get($task_ishasdone_key);
        var_dump($ishasdone);
	if($ishasdone === null){
		$device_arr = getDeviceInfo($session);
		if(!$device_arr['deviceid']){
                    return -1;
		}
		$device_arr['appversion'] = '6500';
		$header = getHeaderInfo($session);
		$data = array(
			'taskType' => $tasktype,
			'uniqCons' => $pkg,
			'pid' => $pid,
			'imei' => $session['DEVICEID'],
			'mac'=>$session['MAC'],
		);
		$extra = array(
			'prefix'=>'task',
			'apiname'=>'/api/tms/task/isHasDone',
			'version' => 'v65',
			'passthrough'=>true
		);
		$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
		$ucenter = load_config('ucenter/'.$app, 'uc');
		$request_serviceId = $ucenter['client_serviceId'];
		$apiname = $extra['apiname'];
		$api_info = array(
			'prefix'=>'task',
			'apiname'=> $apiname,
			'passthrough'=> true,
			'sid'=> $session['UCENTER_SID'],
		);
		$data['serviceId'] = $request_serviceId;
		$data_array = array(
			'data'=> $data,
			'device'=>$device_arr,
			'header'=>$header
		);
		load_helper('ucenter');		
                //var_dump($api_info,$data_array, $extra);
		$result = request_task($api_info,$data_array, $extra);	
                //writelog('red_package_'.$aid.'.log','java:'.json_encode($result));
                writelog('red_package_'.$aid.'.log','java:'.print_r($api_info, true) . "\n" .print_r($data_array, true) . "\n" .print_r($extra, true) . "\n" . json_encode($result) . "\n\n");
		$ishasdone = $result;
                //var_dump($result);
		$redis->set($task_ishasdone_key,$ishasdone,3600);
	}
	return $ishasdone;
}

function getHeaderInfo($session){
	$header = array('appchannel'=>$session['CHANNEL_ID']);
	return $header;
}

function getDeviceInfo($session){
	$device_arr = array(
		'deviceid'=>$session['DEVICEID'],
		'osver'=>$session['FIRMWARE'],
		'nettype'=>$session['NET_TYPE'],
		'netserver'=>$session['MODEL_OID'],
		'screen'=>$session['RESOLUTION'],
		'imsi'=>$session['USER_IMSI'],
		'mac'=>$session['MAC'],
		'ip'=>$session['ip'],
		'abi'=>$session['ABI'],
		'appversion' => $session['VERSION_CODE']
	);
	return $device_arr;
}
