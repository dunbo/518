<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
//$active_id =312;
$sid = $_POST['sid'];
$active_id =  $_POST['aid'];
session_begin();
$url = "http://promotion.anzhi.com/lottery/october_sign/index.php?aid={$active_id}&sid={$sid}";
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
$tm_con = get_config($active_id,$uid);
$time = time();
if($_POST){
	//用户签到日志
	$log_data = array(
		'uid'=>$uid,
		'username' => $_SESSION['USER_NAME'],
		'sigin_phone' => $_POST['mobile_num'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'time' => $time,
		'activity_id' => $active_id,
		'key' => 'sign'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
    $redis->set('october_mobile_num'.$uid,$_POST['mobile_num'],86400*10);
	$tm_con[date('Y-m-d')]['status'] = 1;
    $redis->set('october_tm_config'.$uid,$tm_con,86400*10);
	exit(json_encode(array('code'=>1,'msg'=>'成功')));
}
//获取活动配置
function get_config($aid,$uid){
	global $model;
	global $redis;	
	//$redis->delete('october_tm_config'.$uid);
	$tm_config = $redis->get('october_tm_config'.$uid);	
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
		$redis->set('october_tm_config'.$uid,$tm_config,10*86400);
	}
	return $tm_config;			
}