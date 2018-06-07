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
if(!isset($active_id)){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$active_id = 468;
	}else{
		$active_id = 482;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/ghost_sign_lottery/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}

$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$_GET['stop'] = 1;
	}
}
//获取活动配置
function get_config($aid,$uid){
	global $model;
	global $redis;	
	$tm_config = $redis->get("ghost_sign_lottery:{$aid}_tm_config:".$uid);	
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
			),
			date("Y-m-d",$start+86400*5) => array(
				'num' => 6,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*5)),
			),
			date("Y-m-d",$start+86400*6) =>array(
				'num' => 7,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*6)),
			),
		);
		$redis->set("ghost_sign_lottery:{$aid}_tm_config:".$uid,$tm_config,10*86400);
	}
	return $tm_config;			
}
//获取抽奖次数
function get_lottery_num($aid,$uid){
	global $model;
	global $redis;		
	$lottery_num = $redis->get("ghost_sign_lottery:{$aid}_rest_lottery_num:".$uid);
	if($lottery_num === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid
			),
			'table' => 'valentine_draw_userinfo',
		);
		$userinfo = $model->findOne($option,'lottery/lottery');
		//剩余抽奖次数
		$lottery_num = $userinfo['draw_data_num']-$userinfo['deduction_num'];
		if($lottery_num < 0){
			$lottery_num = 0;
		}
		$redis->set("ghost_sign_lottery:{$aid}_rest_lottery_num:".$uid,intval($lottery_num),10*86400);		
	}	
	return $lottery_num;		
}
//添加用户、修改用户信息
function add_user($data,$time){
	global $model;
	global $redis;		
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
			if($rest < 0){
				$rest = 0;
			}
			$redis->set("ghost_sign_lottery:{$data['aid']}_rest_lottery_num:".$data['uid'],$rest,10*86400);			
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
			$redis->set("ghost_sign_lottery:{$data['aid']}_rest_lottery_num:".$data['uid'],intval($data['draw_data_num']),10*86400);	
		}
    }
    $redis->set("ghost_sign_lottery:{$data['aid']}_userinfo:".$data['uid'],$new_data,86400*10);
	return 	$ret;
}

function get_now_time(){
	global $model;	
	$option = array(
		'where' => array(
			'status' => 1,
			'conf_id' => 294,
		),
		'table' => 'pu_config',
		'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}

function get_soft_info($pkg){
	global $model;	
	$option = array(
		'where' => array(
			'status' => 1,
			'hide' => 1,
			'package' => $pkg
		),
		'table' => 'sj_soft',
		'field' => 'softid,softname,version_code',
		'order' => 'softid desc',
		'cache_time' => 86400		
	);
	$list = $model->findOne($option);
	$option = array(
		'where' => array(
			'softid' => $list['softid'],
		),
		'table' => 'sj_soft_file',
		'field' => 'filesize',
		'cache_time' => 86400				
	);
	$list_file = $model->findOne($option);	
	$list['size'] = $list_file['filesize'];
	return $list;	
}