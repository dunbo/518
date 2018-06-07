<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once 'flyback_fun.php';

$active_id =346;

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
}else{
	$h_str = '';
}
$build_query = http_build_query($_GET);
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/flyback/flyback_end.php?".$build_query;

$tplObj -> out['login_url'] = $login_url;


$prize = getUserAward($redis,$model,200);

$tplObj -> out['prize'] = $prize;
session_begin();

if (!empty($_COOKIE['_AZ_COOKIE_'])) {
	$ucenter = new GoUcenter('www');
	$cookie_data = $ucenter->parse_uc_cookie();
	if (empty($_SESSION['USER_ID']) || $_SESSION['USER_ID'] != $cookie_data['pid']) {
		$user = $ucenter->token_userinfo();
		if (isset($user['USER_ID']) && isset($user['USER_NAME'])) {
			$_SESSION['USER_ID'] = $user['USER_ID'];
			$_SESSION['USER_UID'] = $user['USER_UID'];
			$_SESSION['USER_NAME'] = $user['USER_NAME'];
			$_SESSION['user_data'] = array(
				'login_account' => $cookie_data['loginAccount'],
				'user_name' => $user['USER_NAME'],
				'userid' => $user['USER_ID'],
			);
			//日志
			$log_data = array(
				'uid' => $user['USER_UID'],
			    'users' =>$user['USER_NAME'],
				'imsi' => $_SESSION['USER_IMSI'],
				'device_id' => $_SESSION['DEVICEID'],
				'activity_id' => $active_id,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $sid,
				'time' => time(),
				'key' => 'login'   
			);
			permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		} else {
			setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
			setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
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

if($_SESSION['USER_UID']){
	
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	
}else{
	$tplObj -> out['is_login'] = 2;
}
// 端外页面，不能用sid，用actsid
$actsid = get_key();//生成actsid
$tplObj -> out['actsid'] = $actsid;
$tplObj -> out['share'] = $_GET['share'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['prize'] = $prize;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('flyback/end.html');
