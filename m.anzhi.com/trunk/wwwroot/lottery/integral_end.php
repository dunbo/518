<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$sid = $_GET['sid'];
$build_query = http_build_query($_GET);
$center_url = "http://i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/integral_end.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;

$active_id =263;//278 518test
session_begin();
if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){//已登录
	if (!empty($_COOKIE['_AZ_COOKIE_'])) {
		$ucenter = new GoUcenter('www');
		$cookie_data = $ucenter->parse_uc_cookie();
		if (empty($_SESSION['user_data']) || $_SESSION['USER_ID'] != $cookie_data['pid']) {
			$user = $ucenter->token_userinfo();
			if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) {
				$_SESSION['USER_ID'] = $user['USER_ID'];
				$_SESSION['USER_UID'] = $user['USER_UID'];
				$_SESSION['USER_NAME'] = $user['USER_NAME'];
				$_SESSION['user_data'] = array(
					'login_account' => $cookie_data['loginAccount'],
					'user_name' => $user['USER_NAME'],
					'userid' => $user['USER_ID'],
				);
			}
		}
	} else {
		if (!empty($_SESSION['user_data'])) {
			unset($_SESSION['USER_ID']);
			unset($_SESSION['USER_UID']);
			unset($_SESSION['USER_NAME']);
			unset($_SESSION['user_data']);
		}
	}
}	
setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => time(),
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	//$uid = '20150507225440vnP2JWbVxE';
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	$end_tm = get_end_tm();
	//在活动结束5天后，自动关闭”个人信息“；
	if(time() > $end_tm){
		$tplObj -> out['end_tm'] = 1;
	}
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}
//中奖信息
$exchange_info = get_exchange_info();
$tplObj -> out['exchange_info'] = $exchange_info;	

$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('lottery/integral_end.html');
	//中奖信息
function get_exchange_info(){
	global $model;
	$option = array(
		'where' => array(
			'status' => 1,
			'aid' => $active_id
		),
		'table' => 'integral_kind_award',
		'field' => 'username,prizename',
		'cache_time' => 86400*5,
	);
	$kind_award = $model->findAll($option,'lottery/lottery');	
	return $kind_award;
}

function get_end_tm(){
	global $model;	
	global $active_id ;
	$option = array(
		'where' => array(
			'status' => 1,
			'id' => $active_id
		),
		'table' => 'sj_activity',
		'field' => 'end_tm',
		'cache_time' => 86400*10,
	);
	$ret = $model->findOne($option);	
	return $ret['end_tm']+5*86400;	
}

