<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/october_sign/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
//$active_id =312;
$active_id = $_GET['aid'];
$sid = $_GET['sid'];

$time = time();
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
user_loging();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	$tplObj -> out['userinfo'] = $redis->get('october_userinfo'.$uid);
	$tplObj -> out['mobile_num'] = $redis->get('october_mobile_num'.$uid);
	$tm_config = get_config($active_id,$uid);
	$i = 0;
	foreach($tm_config as $k => $v){
		if($v['status'] == 1){
			$i++;
		}
	}
	$tplObj -> out['sign_day'] = $i;
	$tplObj -> out['tm_config'] = $tm_config;
	if($tm_config[date('Y-m-d')]['status'] == 1){
		$tplObj -> out['is_sing'] = 1;//当天是否签到过(1是2否)
	}else{
		$tplObj -> out['is_sing'] = 2;//当天是否签到过
	}
}else{//未登录
	$tplObj -> out['num'] = array(1,2,3,4,5,6,7);
	$tplObj -> out['is_login'] = 2;
}
if($_GET['stop'] == 1){
	$tpl = "lottery/october_sign/end.html";
}else{
	$tpl = "lottery/october_sign/index.html";
}
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);

function user_loging(){
	if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
		if (!empty($_COOKIE['_AZ_COOKIE_'])) {
			$ucenter = new GoUcenter('www');
			$cookie_data = $ucenter->parse_uc_cookie();
			if ($_SESSION['USER_ID'] != $cookie_data['pid']) {
				$user = $ucenter->token_userinfo();
				if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) {
					$_SESSION['USER_ID'] = $user['USER_ID'];
					$_SESSION['USER_UID'] = $user['USER_UID'];
					$_SESSION['USER_NAME'] = $user['USER_NAME'];
				}
			}
		}
	}	
	setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
	setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
}

//获取活动配置
function get_config($aid,$uid){
	global $model;
	global $redis;	
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