<?php
/**
 * --------------------------------------------------------------------------------------------------
 * Date: 2015-10-28
 * 11月回归安智100%有礼 派现金红包活动
 * -------------------------------------------------------------------------------------------------- 
 */
$active_id =346;

include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once 'flyback_fun.php';
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/flyback/flyback.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
//echo $redis->get('flyback_gift_num');
//echo "<br>";

$model = new GoModel();
$sid = $_GET['sid'];

session_begin();
//var_dump($_COOKIE);
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

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
	//$uid = '20150330130627TU01y592gH';
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	$lottery_num = get_user_draw($redis,$model,$uid);    
}else{//未登录
	$lottery_num = 0;
	$tplObj -> out['is_login'] = 2;
}
//if($_SESSION['USER_UID']=='20150120101555348M6imQDc'){
//	var_dump($_SESSION['USER_UID']);
//	$redis -> set("flyback_num_uid_".$uid,1);
//}

$prize = getUserAward($redis,$model);
if(count($prize)==0) $prize = '';
//活动页面打开日志
$log_data = array(
        'uid' => $_SESSION['USER_UID'],
        'users' =>$_SESSION['USER_NAME'],
        'imsi' => $_SESSION['USER_IMSI'],
        'device_id' => $_SESSION['DEVICEID'],
        'activity_id' => $active_id,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'sid' => $sid,
        'time' => time(),
        'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
// 端外页面，不能用sid，用actsid
$actsid = get_key();//生成actsid
$tplObj -> out['actsid'] = $actsid;
$tplObj -> out['share'] = $_GET['share'];
$tplObj -> out['lottery_num'] = $lottery_num;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['prize'] = $prize;
$tplObj -> out['uid'] = $_SESSION['USER_UID'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('flyback/index.html');
