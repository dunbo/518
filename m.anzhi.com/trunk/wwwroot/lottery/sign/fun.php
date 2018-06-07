<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!$_GET['ap_id'] && !ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$prefix = "sign";
if($configs['is_test'] == 1 ) {
	$time  = get_now_time();
}else {
	$time  = time();
}
$today = date('Y-m-d',$time);


$activity_host = $configs['activity_url'];
$stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];
if($stop != 1 && !$_GET['ap_id']){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
//获取配置
function get_config($ap_id){
	global $active_id;
	global $model;
	if($ap_id){
		$activity_page_id = $ap_id;
	}else{	
		$option = array(
			'where' => array(
				'id' => $active_id,
			),
			'table' => 'sj_activity',
			'field' => 'name,activity_page_id,activity_end_id,start_tm,end_tm,channel_id',
			'cache_time' => 30*60
		);
		$activity = $model->findOne($option);	
		if($activity['end_tm'] <= time()){
			$activity_page_id = $activity['activity_end_id'];//结束pageid
		}else{
			$activity_page_id = $activity['activity_page_id'];
		}
		//活动天数
		$act_day = intval(($activity['end_tm']-$activity['start_tm'])/86400);	
	}
	$option = array(
		'where' => array(
			'ap_id' => $activity_page_id,
		),
		'table' => 'sj_activity_page',
		'cache_time' => 10*60,
	);
	$list = $model->findOne($option);	
	$list['acrivity_name'] = $activity['name'];
	$list['start_tm'] = $activity['start_tm'];
	$list['end_tm'] = $activity['end_tm'];
	$list['channel_id'] = $activity['channel_id'];
	$list['acrivity_day'] = $act_day;	
	return $list;			
}

function sign_config($uid,$start_tm,$day_num){
	global $redis;	
	global $prefix;
	global $active_id;
	$con_key = "{$prefix}:{$active_id}:tm_config:".$uid;
	$tm_config = $redis->get($con_key);	
	if($tm_config === null){
		//已经签到和补签的数据
		$already_sign = get_sign_data($uid);
		$tm_config = array();
		for($i=0;$i<$day_num;$i++){
			$start = $start_tm+$i*86400;
			$key = date("Y-m-d",$start);
			$tm_config[$key]['level'] = $i+1;
			$tm_config[$key]['status'] = $already_sign[$key]['status'] ? $already_sign[$key]['status'] : 0;
		}
		$redis->set($con_key,$tm_config, 60*86400);
	}
	return $tm_config;			
}
//获取已经签到和补签的数据
function get_sign_data($uid){
	global $model;
	global $active_id;	
	$option = array(
		'where' => array('uid'=>$uid,'aid'=>$active_id),
		'table' => 'sign_user_data',
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$new_arr = array();
	foreach($list_arr as $key => $val){
		$new_arr[$val['sign_date']]['status'] = $val['status'];
	}
	unset($list_arr);
	return $new_arr;
}
//获取签到icon
function get_sign_icon(){
	global $model;
	global $redis;
	global $active_id;		
	global $prefix;		
	$sign_icon_key = "{$prefix}:{$active_id}:sign_icon";
	$sign_icon = $redis->get($sign_icon_key);	
	if($sign_icon === null){	
		$option = array(
			'where' => array('status' =>1,'aid'=>$active_id),
			'table' => 'sign_prize_icon',
			'order' => 'level asc',
		);
		$list_arr = $model->findAll($option,'lottery/lottery');	
		$sign_icon = array();
		foreach($list_arr as $k =>$v){
			$sign_icon[$v['level']] = $v;
		}
		$redis->set($sign_icon_key,$sign_icon, 60*30);
	}
	return 	$sign_icon;
}
//签到奖品
function sign_prize($uid,$level,$activityName){
	global $model;
	global $redis;	
	global $prefix;	
	global $active_id;	
	load_helper('task');
	$task_client = get_task_client();
	$new_array=array(
		'prefix' => $prefix,
		'uid'	 =>	$uid,
		'aid'  => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'position' => $level,
		'activityName' => $activityName,
		'table' => 'sign_prize_icon',
		'table_award' => 'sign_award',
	);
	$the_award = $task_client->do('lssue_prize', json_encode($new_array));
	return json_decode($the_award,true);	
}
//获取签到天数
function get_sign_days($uid,$sign_type,$conf_arr,$day){
	$status	= 'status';	
	//连续签到
	if($sign_type ==1){
		$array =  array();
		$new_arr =  array();
		$index = 0;		
		foreach($conf_arr as $k => $v){
			if($v['level'] > $day) break;
			$array[$k][$status] = $v[$status];			
			if($v[$status] >= 1 &&  $array[$k][$status] >=1){
				$index++;
			}else{
				if($index) $new_arr[] = $index;
				$index = 0;				
			}
		}	
		if($index) $new_arr[] = $index;
		$pos = array_search(max($new_arr), $new_arr);
		$index = $new_arr[$pos];		
	//签到总天数	
	}else if($sign_type ==2){
		$index = 0;		
		foreach($conf_arr as $k => $v){
			if($v['level'] > $day) break;	
			if($v[$status] >= 1){
				$index++;
			}
		}
	}
	return $index;
}

//用户剩于抽奖次数
function user_lottery_num($uid){
	global $model;
	global $redis;	
	global $prefix;	
	global $active_id;	
	$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$lottery_num = $redis->get($lottery_num_key);
	if($lottery_num === null){	
		$option = array(
			'table' => 'sign_userinfo',
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id,
			),
			'field' => 'lottery_num,deduction_num'
		);
		$list = $model->findOne($option,'lottery/lottery');	
		//剩于抽奖次数
		$lottery_num = intval($list['lottery_num']-$list['deduction_num']);
		if($lottery_num < 0){
			$lottery_num = 0;
		}
		$redis->set($lottery_num_key,$lottery_num,15*60);
	}
	return $lottery_num;
}

//送抽奖机会
function add_lottery_num($uid,$limit_num,$key,$expire = 86400,$act_day){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	global $today;		
	global $time;		
	//var_dump($expire);exit;
	$login_num = $redis->setnx($key,1,$expire);	
	if($login_num === true){
		//用户每日获得抽奖次数限制
		$day_limit_key = "{$prefix}:".$active_id.":limit_lottery_num:".$uid.":".$today;
		$day_limit =  $redis->get($day_limit_key);
		if(intval($day_limit) >= intval($limit_num)){
			return false;
		}	
		$where = array(
			'uid' => $uid,
			'aid' => $active_id,
		);
		$option = array(
			'table' => 'sign_userinfo',
			'where' => $where,
			'field' => 'lottery_num,deduction_num'
		);
		$rest_list = $model->findOne($option,'lottery/lottery');		
		$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
		if($rest_list){
			if($rest_list['lottery_num'] > ($act_day*$limit_num)){
				return false;
			}		
			$data_update = array(
				'lottery_num' => array('exp',"`lottery_num`+1"),
				'update_tm' => $time,
				'__user_table' => 'sign_userinfo'
			);
			$ret = $model -> update($where,$data_update,'lottery/lottery');	
			//file_put_contents('/tmp/act_z.log',"limit:".$limit_num." day_limit:".$day_limit."\n".$model->getsql(),FILE_APPEND);
			if($ret){	
				$lottery_num = $redis->get($lottery_num_key);
				if($lottery_num === null){
					$lottery_num = intval($rest_list['lottery_num'])-intval($rest_list['deduction_num'])+1;
					$redis->set($lottery_num_key,intval($lottery_num),15*60);	
				}else{
					$redis->setx('incr',$lottery_num_key,+1);
				}
				if($day_limit === null ){
					$redis->set($day_limit_key,1,86400);
				}else{
					$redis->setx('incr',$day_limit_key,+1);
				}
				$redis->expire($key,86400*20);						
			}else{
				return false;
			}				
		}else{
			$data = array(
				'uid' => $uid,
				'aid' => $active_id,
				'username' => $_SESSION['USER_NAME'],
				'lottery_num' => 1,
				'update_tm' => $time,
				'create_tm' => $time,
				'os_from' => 2,
				'__user_table' => 'sign_userinfo'
			);
			$ret =  $model->insert($data,'lottery/lottery');			
			if($ret){
				$redis->expire($key,86400*20);
				$redis->set($lottery_num_key,1,15*60);
				if($day_limit ===null ){
					$redis->set($day_limit_key,1,86400);
				}else{
					$redis->setx('incr',$day_limit_key,+1);
				}	
				//用于初始化用户缓存				
				$redis->set("{$prefix}:{$active_id}_userinfo:".$uid,$data,60*86400);
			}else{
				return false;
			}
		}
	}else{
		return false;
	}
}
//获取奖品名
function get_prize_name($aid){
	global $model;	
	global $redis;	
	global $prefix;	
	$key_prize_name = $prefix.":".$aid.":prize_name";
	$key_prize_level = $prefix.":".$aid.':prize_level';
	$prize_name = $redis->get($key_prize_name);
	$new_prize_level = $redis->get($key_prize_level);
	if($prize_name === null || $new_prize_level === null){
		$option = array(
			'where' => array('status' =>1,'aid'=>$aid),
			'table' => 'sign_prize',
			'field' => 'name,id,level,pic_path',
			'order' => 'level asc',
		);
		$list_arr = $model->findAll($option,'lottery/lottery');	
		$prize_name = array();
		$prize_level = array();
		foreach($list_arr as $v){
			$prize_name[$v['id']] = $v;
			$prize_level[$v['level']] = $v;
		}
		ksort($prize_level);	
		unset($list_arr);
		$i = 0;
		$new_prize_level = array();
		foreach($prize_level as $k => $v){
			$i++;
			$prize_name[$v['id']]['i'] = $i;
			$new_prize_level[] = $v;
		}
		unset($prize_level);
		$redis->set($key_prize_name,$prize_name,30*60);
		$redis->set($key_prize_level,$new_prize_level,30*60);
	}
	return array($prize_name,$new_prize_level);
}
//领取的奖品
function get_receive_prize($map,$uid){
	global $prefix;
	global $redis;
	global $active_id;		
	$receive_prize_key = "{$prefix}:{$active_id}:receive_prize:".$uid;
	$receive_prize = $redis->get($receive_prize_key);
	if($receive_prize === null){	
		$arr = explode(',',$map);
		$receive_prize = array();
		foreach($arr as $k =>$v){
			$receive_prize[$v] = 0;
		}
		$redis->set($receive_prize_key,$receive_prize, 60*86400);
	}
	return 	$receive_prize;	
}
//添加用户签到、补签数据
function add_sign_data($uid,$sign_date,$status){
	global $active_id;	
	global $model;
	global $time;
	$where = array(
		'uid' => $uid,
		'aid' => $active_id,
		'sign_date' => $sign_date,
	);
	$option = array(
		'table' => 'sign_user_data',
		'where' => $where,
	);
	$rest_list = $model->findOne($option,'lottery/lottery');	
	if(!$rest_list){	
		$data = array(
			'uid' => $uid,
			'aid' => $active_id,
			'sign_date' => $sign_date,
			'add_tm' => $time,
			'status' => $status,
			'__user_table' => 'sign_user_data'
		);
		$ret =  $model->insert($data,'lottery/lottery');	
	}	
}
//获取用户的补签次数
function get_retroactive_num($uid,$free_retroactive_num,$azb_retroactive_num){
	global $prefix;
	global $redis;
	global $active_id;		
	$free_key = "{$prefix}:{$active_id}:free_retroactive_num:".$uid;
	$free_retroactive_user_num = $redis->get($free_key);
	if($free_retroactive_user_num === null){	
		$free_retroactive_user_num = $free_retroactive_num;
		$redis->set($free_key,intval($free_retroactive_num),60*86400);
	}	
	$azb_retroactive_key = "{$prefix}:{$active_id}:azb_retroactive_num:".$uid;
	$azb_retroactive_user_num = $redis->get($azb_retroactive_key);
	if($azb_retroactive_user_num === null){	
		$azb_retroactive_user_num = $azb_retroactive_num;
		$redis->set($azb_retroactive_key,intval($azb_retroactive_num),60*86400);
	}
	return array($free_retroactive_user_num,$azb_retroactive_user_num);
}
//扣除用户补签次数
function save_retroactive_num($uid,$pwd,$azbAmount,$channel_id){
	global $prefix;
	global $redis;
	global $active_id;		
	$free_key = "{$prefix}:{$active_id}:free_retroactive_num:".$uid;
	$free_retroactive_user_num = $redis->get($free_key);
	if($free_retroactive_user_num > 0){
		//优先扣除免费的补签次数
		$now_num = $redis->setx('incr',$free_key,-1);
		if($now_num < 0){
			$redis->set('incr',$free_key,0);
		}
		$money = 0;
	}else{
		//扣除安智币补签
		$azb_retroactive_key = "{$prefix}:{$active_id}:azb_retroactive_num:".$uid;
		$azb_retroactive_user_num = $redis->get($azb_retroactive_key);
		if($azb_retroactive_user_num > 0){
			$az_money = get_azmoney($uid);
			if($az_money['azmoney'] < $azbAmount){
				$ret_arr = array(
					'code'=>0,
					'msg'=>'安智币余额为'.$az_money['azmoney'].'，请充值后进行补签操作；'
				);
				return $ret_arr;				
			}
			//消费安智币(必须先扣安智币除才能执行下面的操作)
			$azb_res = azb_consume($uid,$pwd,$azbAmount,$channel_id);
			if($azb_res['code'] == 0){
				$ret_arr = array(
					'code'=>0,
					'msg'=>$azb_res['msg']
				);
				return $ret_arr;
			}			
			$now_num = $redis->setx('incr',$azb_retroactive_key,-1);
			if($now_num < 0){
				$redis->set('incr',$azb_retroactive_key,0);
				$ret_arr = array(
					'code'=>0,
					'msg'=>'抱歉，您的补签次数已经用完！'
				);
				return $ret_arr;
			}
		}else{
			$ret_arr = array(
				'code'=>0,
				'msg'=>'抱歉，您的补签次数已经用完！'
			);
			return $ret_arr;
		}
		$money = $azbAmount;
	}
	$ret_arr = array(
		'code'=>1,
		'money' => $money
	);
	return $ret_arr;
}


function device_user_num($uid){
	global $redis;		
	global $active_id;		
	global $prefix;	
	$device_user = $prefix.":".$active_id.':device_user:'.$_SESSION['DEVICEID'];
	$uid_arr = $redis->get($device_user);	
	$uid_arr[$uid] = 1;
	$redis->set($device_user,$uid_arr,60*86400);	
	$total = count($uid_arr);
	return $total;
}


 //获取用户安智币
function get_azmoney($uid){
	global $active_id;		
	$urlParam = '/user/azb/get';
	$log_name = "azb_get_".$active_id.".log";
	$js_data = array(
		'uid'	=>	$uid,
	);
	$data = array(
		'serviceId'	=>	'014',
		'data'		=>	json_encode($js_data)
	);	
	$res = get_money_data($data, $urlParam,$log_name);
	if($res['code'] != '200') {
		$ret_arr = array(
				'code'	=>	0,
				'msg'	=>	$res['msg'],
		);
		return $ret_arr;
	}else {
		$res_info = json_decode($res['data'], true);
		$ret_arr = array(
			'code'		=>	1,
			'msg'		=>	$res['msg'],
			'azmoney'		=>	isset($res_info['azmoney']) ? $res_info['azmoney'] : 0,
			'isHasPayPwd'	=>	isset($res_info['isHasPayPwd']) ? $res_info['isHasPayPwd'] : 0,
		);
		return $ret_arr;
	}	
}
//消费安智币
function azb_consume($uid,$pwd,$azbAmount,$channel_id){
	global $active_id;	
	global $sid;	
	global $time;	
	global $model;		
	//消费安智币
	$urlParam = '/user/azb/consume';
	$log_name = "azb_consume_".$active_id.".log";
	$js_data = array(
			'uid'		=>	$uid,
			'appkey'	=>	'1392365303Jy1R97taJfdtops8Cxum',
			'orderDes'	=>	'补签消费',
			'payPwd'	=>	$pwd,
			'azbAmount'	=>	$azbAmount
	);
	$data = array(
		'serviceId'	=>	'014',
		'data'		=>	json_encode($js_data)
	);
	$ret = get_money_data($data, $urlParam,$log_name);	
	//消费日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'azbAmount' => $azbAmount,//安智币
		'uid'=>$uid,
		'return_code'   => 	$ret['code'],
		'return_msg'    => $ret['msg'],
		'key' => 'azb_consume',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	if($ret['code'] != '200') {
		$ret_arr = array(
				'code'	=>	0,
				'msg'	=>	$ret['msg'],
		);
		return $ret_arr;
	}else {	
        $new_data = array(
			'uid' => $uid,
			'aid' => $active_id,
			'money' => $azbAmount,
			'add_tm' => $time,
			'__user_table' => 'consume'
        );	
		if($channel_id){
			$channel_arr = explode(",",substr($channel_id,1,-1));
			if(in_array('3150',$channel_arr) || in_array('271',$channel_arr)){
				$new_data['is_test'] = 1;
			}
		}
        $model->insert($new_data,'lottery/lottery');
		//抽奖成功日志
		$log_data = array(
			"imsi"			=>	$_SESSION['USER_IMSI'],
			"device_id"		=>	$_SESSION['DEVICEID'],
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],
			"activity_id"	=>	$active_id,
			"ip"			=>	$_SERVER['REMOTE_ADDR'],
			"sid"			=>	$sid,
			"time"			=>	$time,
			"user"			=>	$_SESSION['USER_NAME'],
			'azbAmount' => $azbAmount,
			'uid'			=>	$uid,
			'key'			=>	'azb_consume_success',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
	}
	return $ret;
}
/**
 * 获取安智币信息接口
 * @param  array $val
 * @return array
 */
function get_money_data($vals, $urlParam,$log_name){
	$host	=	load_config('pay_host');
	$url	=	$host.'/pay/internal'.$urlParam;
	$vals	=	http_build_query($vals);
	$res	=	httpGetInfo($url,$host, $vals,$log_name);
	$last	=	json_decode($res,true);
	return $last;
}

function get_now_time(){
	global $model;
	$option = array(
		'where' => array(
				'status'  => 1,
				'conf_id' => 294
		),
		'table' => 'pu_config',
		'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}