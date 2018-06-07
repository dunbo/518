<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"zx_sign";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];

$model = new GoModel();
if($configs['is_test'] == 1 ){
	$time  = get_now_time();
}else {
	$time  = time();
}

$activity_host = $configs['activity_url'];

if($_GET['stop'] != 1 ){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id;
		header("Location: {$url}");
	}
}


//获取活动配置
function get_config($aid, $uid){
	global $model;
	global $redis;	
	global $prefix;
	global $time;
	
	$tm_config = $redis->get("{$prefix}:{$aid}_tm_config:".$uid);	
	if($tm_config === null){
		$option = array(
			'where' => array(
				'id' => $aid,
			),
			'table' => 'sj_activity',
			'field' => 'start_tm',
		);
		$activity = $model->findOne($option);
		$start =  $activity['start_tm'];
		$tm_config = array(
			date("Y-m-d",$start) => array(
				'num' => 1,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start)),
			),
			date("Y-m-d",$start+86400) => array(
				'num' => 2,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400)),
			),
			date("Y-m-d",$start+86400*2) => array(
				'num' => 3,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*2)),
			),
			date("Y-m-d",$start+86400*3) => array(
				'num' => 4,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*3)),
			),
			date("Y-m-d",$start+86400*4) => array(
				'num' => 5,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*4)),
			)
		);
		
		$redis->set("{$prefix}:{$aid}_tm_config:".$uid, $tm_config, 8*86400);
	}
	return $tm_config;			
}

/**
 * 剩余抽奖次数
 * @param int $uid
 * @return array
 */
function get_lottery_num($uid){
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	
	$lottery_num = $redis->get("{$prefix}:{$active_id}_rest_lottery_num:".$uid);
	if($lottery_num === null){
		$option = array(
			'where' => array(
				'uid'	=>	$uid,
				'aid'	=>	$active_id
			),
			'table'	=>	'valentine_draw_userinfo',
		);
		$userinfo = $model->findOne($option,'lottery/lottery');
		//剩余抽奖次数
		$lottery_num = $userinfo['draw_data_num'] - $userinfo['deduction_num'];
		if($lottery_num < 0) {
			$lottery_num = 0;
		}
		$redis->set("{$prefix}:{$active_id}_rest_lottery_num:".$uid, intval($lottery_num), 10*86400);		
	}	
	return $lottery_num;		
}

/**
 * 添加用户、修改用户信息
 */
function add_user($data,$time){
	global $model;
	global $redis;
	global $prefix;
	
	$option = array(
			'where' => array(
					'uid' => $data['uid'],
					'aid' => $data['aid']
			),
			'table' => 'valentine_draw_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');
	if($userinfo){
		$new_data = array(
				'uid' => $data['uid'],
				'username' => $_SESSION['USER_NAME'],
				'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
				'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
				'address' => $data['address'] ? $data['address'] : $userinfo['address'],
				'update_tm' => $time,
				'__user_table' => 'valentine_draw_userinfo'
		);
		if($data['draw_data_num']){
			$new_data['draw_data_num'] = $data['draw_data_num'];
		}else{
			$new_data['draw_data_num'] = $userinfo['draw_data_num'];
		}
		$where = array(
				'uid' => $data['uid'],
				'aid' =>$data['aid'],
		);
		$ret =  $model->update($where, $new_data,'lottery/lottery');
		if($ret){
			//剩余抽奖次数
			$rest = intval($new_data['draw_data_num']-$userinfo['deduction_num']);
			if($rest < 0) {
				$rest = 0;
			}
			$redis->set("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'], $rest, 10*86400);
		}
	}else {//新增
		$new_data = array(
				'uid' => $data['uid'],
				'aid' => $data['aid'],
				'username' => $_SESSION['USER_NAME'],
				'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
				'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
				'address' => $data['address'] ? $data['address'] : $userinfo['address'],
				'update_tm' => $time,
				'create_tm' => $time,
				'os_from' => 2,
				'__user_table' => 'valentine_draw_userinfo'
		);
		if($data['draw_data_num']){
			$new_data['draw_data_num'] = $data['draw_data_num'];
		}
		$ret =  $model->insert($new_data,'lottery/lottery');
		if($ret){
			$redis->set("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'], intval($data['draw_data_num']),  30*60);
		}
	}
	$redis->set("{$prefix}:{$data['aid']}_userinfo:".$data['uid'], $new_data, 86400*10);
	return 	$ret;
}
function get_gift_pid(){
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	$key = "{$prefix}:{$active_id}:gift_pid";
	$pid = $redis->get($key);
	if($pid === null){
		$option = array(
			'where' => array(
				'aid'	=>	$active_id,
				'type' =>5
			),
			'table'	=>	'valentine_draw_prize',
		);
		$prizelist = $model->findOne($option,'lottery/lottery');
		$pid = $prizelist['id'];
		$redis->set($key,$pid,30*60);		
	}	
	return $pid;		
	
}

function get_user_award($uid){
	global $model;
	global $redis;
	global $prefix;
	global $active_id;	
	$gift_key = "{$prefix}:{$active_id}_gift_prize:{$uid}";
	$award_key = "{$prefix}:{$active_id}_draw_award:{$uid}";
	$gift_award_list = $redis->getlist($gift_key);
	$kind_award_list = $redis->getlist($award_key);
	if(!$gift_award_list  || !$kind_award_list ){
		$redis->delete($gift_key);
		$redis->delete($award_key);
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id,
			),
			'table' => 'valentine_draw_award',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$pid = get_gift_pid();
		$kind_award_list = array();
		$gift_award_list = array();
		foreach((array)$kind_award as $k => $v){
			if(intval($v['pid']) == $pid){
				list($prizename,$gift_num) = explode(":",$v['prizename']);
				$gift_award_list[$v['id']]['uid'] = $uid;
				$gift_award_list[$v['id']]['softname'] = '诛仙';
				$gift_award_list[$v['id']]['package'] = 'com.wanmei.zhuxian.anzh';
				$gift_award_list[$v['id']]['prizename'] = $prizename;
				$gift_award_list[$v['id']]['gift_number'] = $gift_num;
				$gift_award_list[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);				
			}else{
				$kind_award_list[$v['id']] = $v;
				$kind_award_list[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
			}
		}
		unset($kind_award);
		$redis -> setlist($award_key,$kind_award_list,30*60);		
		$redis -> setlist($gift_key,$gift_award_list,30*60);		
	}else{
		foreach($gift_award_list as $k => $v){
			$gift_award_list[$k] = json_decode($v,true);
		}	
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}	
	}
	return array($gift_award_list,$kind_award_list);
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


