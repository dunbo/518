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
$prefix = "pre_down_operation";
$time = time();
$today = date('Ymd');
if($configs['is_test'] == 1 ) {
	$activity_host = 'http://m.test.anzhi.com';
}else{
	$activity_host = 'http://promotion.anzhi.com';
}
$stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];
if($stop != 1 && !$_GET['ap_id']){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
//获取配置
function get_config($aid,$ap_id){
	global $model;
	if($ap_id){
		$activity_page_id = $ap_id;
	}else{	
		$option = array(
			'where' => array(
				'id' => $aid,
			),
			'table' => 'sj_activity',
			'field' => 'name,activity_page_id,activity_end_id,start_tm,end_tm',
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
	$list['is_score'] = intval($list['is_score']);
	$list['acrivity_name'] = $activity['name'];
	$list['acrivity_day'] = $act_day;
	$list['down_addlotterynum_switch'] = intval($list['down_addlotterynum_switch']);
	return $list;			
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
			'table' => 'pre_down_operation_userinfo',
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
			'table' => 'pre_down_operation_prize',
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
			'table' => 'pre_down_operation_userinfo',
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
				'__user_table' => 'pre_down_operation_userinfo'
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
				'__user_table' => 'pre_down_operation_userinfo'
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
				$redis->set("{$prefix}:{$active_id}_userinfo:".$uid,$data,10*86400);
			}else{
				return false;
			}
		}
	}else{
		return false;
	}
}
function get_gift_db_pkg(){
	global $model;	
	global $redis;		
	global $active_id;		
	global $prefix;	
	$pkg_key = "{$prefix}:{$active_id}:pkg";
	$prize_gift_pkg = $redis->get($pkg_key);
	if($prize_gift_pkg === null ){
		$option = array(
			'where' => array('status' =>0,'aid'=>$active_id),
			'table' => 'pre_down_operation_gift',
			'field' => 'package',
			'group' => 'package'
		);
		$list_arr = $model->findAll($option,'lottery/lottery');		
		$prize_gift_pkg = array();
		foreach($list_arr as $v){
			$prize_gift_pkg[] = $v['package'];
		}
		$redis->set($pkg_key,$prize_gift_pkg,86400*10);
	}
	return $prize_gift_pkg;
}
//a.优先抽中已安装游戏的虚拟礼包；
//b.同一用户不重复中同一款礼包；
//c.若用户抽奖的次数，超过了虚拟礼包的种类数量，则随机中虚拟礼包。
function get_gift_pkg_new($uid,$gift_pkg,$pid){
	global $redis;		
	global $active_id;		
	global $prefix;		
	$gift_pkg_key = "{$prefix}:{$active_id}:gift_pkg:".$uid;
	$user_gift_pkg = $redis->get($gift_pkg_key);
	$open_gift_pkg = explode(";",$gift_pkg);
	//file_put_contents("/tmp/christmas.log",var_export($open_gift_pkg,true)."\n".var_export($user_gift_pkg,true),FILE_APPEND);
	if(!$user_gift_pkg){
		$prize_gift_pkg = $redis->get("{$prefix}:{$active_id}:pkg");
		$redis -> set($gift_pkg_key,$prize_gift_pkg,10*86400);
        $user_gift_pkg = $prize_gift_pkg;
		$intersection =  array_intersect($open_gift_pkg, $prize_gift_pkg);
	}else{	
		$intersection =  array_intersect($open_gift_pkg, $user_gift_pkg);
	}
	if($intersection){
		//a.优先抽中已安装游戏的虚拟礼包；
		foreach($intersection as $v){
			return $v;
			exit;
		}
	}else{
		return $user_gift_pkg[0]; 
	}
}

//去除已获得的礼包包名
function del_gift_pkg_new($uid,$pkg){
	global $redis;		
	global $active_id;		
	global $prefix;	
	$gift_pkg_key = "{$prefix}:{$active_id}:gift_pkg:".$uid;
	$user_gift_pkg = $redis->get($gift_pkg_key);	
	$new_gift_pkg = array();
	foreach($user_gift_pkg as $k => $v){
		if($v != $pkg){
			$new_gift_pkg[] = $v;
		}
	}
	//file_put_contents("/tmp/christmas.log",var_export($new_gift_pkg,true),FILE_APPEND);
	$redis -> set($gift_pkg_key,$new_gift_pkg,10*86400);
}


function device_user_num($uid){
	global $redis;		
	global $active_id;		
	global $prefix;	
	$device_user = $prefix.":".$active_id.':device_user:'.$_SESSION['DEVICEID'];
	$uid_arr = $redis->get($device_user);	
	$uid_arr[$uid] = 1;
	$redis->set($device_user,$uid_arr,10*86400);	
	$total = count($uid_arr);
	return $total;
}