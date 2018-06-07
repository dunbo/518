<?php
/**
 * --------------------------------------------------------------------------------------------------
 * Date: 2015-10-28
 * 11月回归安智100%有礼 派现金红包活动
 * -------------------------------------------------------------------------------------------------- 
 */
 
 //用户收货地址
include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once 'flyback_fun.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
//没有session 跳转到首页
session_begin();
$sid = $_GET['sid']?$_GET['sid']:$_POST['sid'];
$active_id = 346;
if(!isset($_SESSION['USER_UID'])){//未登录 跳转到首页
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
    }else{
        echo '0';
		exit();
    }
}

if($_POST['uid']){
	//获取收货地址
	if($_POST['type']=='get_info'){
		$user_info = get_user_info($redis,$_POST['uid']);		
		echo json_encode($user_info);
		exit();
	}else if($_POST['type']=='save_info'){
		$data = array(
                'name' => $_POST['name'],
                'mobile' => $_POST['mobile'],
                'address' => $_POST['address'],
				'activity_id'=>$active_id,
				'sid' => $sid
        );
		update_user_info($redis,$_POST['uid'],$data);
		echo '1';
		exit();
	}else if($_POST['type']=='get_winner'){
		//获取奖品
		$myAward = getMyAward($redis,$_POST['uid']);
		echo json_encode($myAward);
		exit();
	}
}else{
	echo '0';
}
echo '1';