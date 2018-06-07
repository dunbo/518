<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
include_once 'rebate_draw_function.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$sid = $_GET['sid'];
$active_id =223;
session_begin();

if (!empty($_COOKIE['_AZ_COOKIE_'])) {
	$ucenter = new GoUcenter('www');
	$cookie_data = $ucenter->parse_uc_cookie();
	if (empty($_SESSION['USER_ID']) || $_SESSION['USER_ID'] != $cookie_data['pid']) {
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
	$lottery_num = get_uid_draw_num($redis,$model,$uid);            
}else{//未登录
	$lottery_num = 0;
	$tplObj -> out['is_login'] = 2;
}



$next_time = $redis -> get("rebate_draw_next_brush_time");
if(!$next_time){
    $date_option = array(
            'where' => array(
                    'config_type' => 'rebate_draw',
                    'status' => 1
            ),
            'table' => 'pu_config'
    );
    $data_date = $model->findOne($date_option);
    $next_time = $data_date['configname']; 
    $redis -> set("rebate_draw_next_brush_time",$next_time,600);    
}

$now_day = date('H:00',strtotime($next_time.'0000')-7200);
$next_day = date('H:00',strtotime($next_time.'0000'));
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


$tplObj -> out['lottery_num'] = $lottery_num;
$tplObj -> out['now_time'] = $now_day;
$tplObj -> out['next_time'] = $next_day;
$tplObj -> out['prize'] = $prize;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> display('rebate_draw/index.html');
