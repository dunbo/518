<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$sid = $_GET['sid'];
if(isset($_GET['tiaozhuan'])){
    header("Location: http://promotion.anzhi.com/lottery/recharge_top_lottery.php?sid=".$sid); 
}
$active_id =216;
session_begin();
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
                                //日志
                                $log_data = array(
                                    'uid' => $user['USER_UID'],
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
            $rs = $redis -> gethash("recharge_top:user");
            $tplObj -> out['user_info'] = $rs[$uid];
            $tplObj -> out['username'] = $_SESSION['USER_NAME'];
            $tplObj -> out['is_login'] = 1;
            $tplObj -> out['uid'] = $uid;
        }else{//未登录
            $tplObj -> out['is_login'] = 2;
        }

if($_SESSION['VERSION_CODE'] < 5300){
	$tplObj -> out['channel_status'] = 1000;
}
$tplObj -> out['sid'] = $_GET['sid'];
//top 10
$user_top = $redis -> get("recharge_top:user_top");
$tplObj -> out['user_top'] = $user_top;
$tplObj -> out['is_save'] = 3;
if(date('Ymd')=='20150505'){
        $is_top10 = 2;
        foreach($user_top as $k=>$v){
            if($v['uid']==$uid){
                $is_top10 = 1;
                $tplObj -> out['top_number'] = $k;
            }
        }
        if($is_top10==1){
                    $rs = $redis->get('recharge_top_is_save_'.$uid);
                    if($rs==null){
                        $tplObj -> out['is_save'] = 2;
                    }else{
                        $tplObj -> out['is_save'] = 1;
                    }
        }
}

$tplObj -> out['now_time'] = $redis -> get("recharge_top:now_time");
$tplObj -> out['next_time'] = $redis -> get("recharge_top:next_time");

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('recharge_top.html');
