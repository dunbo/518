<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
include_once 'rebate_draw_function.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
//没有session 跳转到首页
session_begin();
$sid = $_GET['sid']?$_GET['sid']:$_POST['sid'];
$active_id = 223;
if(isset($_SESSION['USER_UID'])){//已登录
	//$uid = '20150330130627TU01y592gH';      
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
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
    }else{
        header("Location: http://promotion.anzhi.com/lottery/rebate_draw.php?sid={$sid}");        
    }
}
$action = $_GET['action']?$_GET['action']:$_POST['action'];
$tplObj -> out['sid'] =  $sid;
if($action == 1 && $_POST['name']){ //填写收货信息
    $_POST['activity_id'] = $active_id;
    $_POST['sid'] = $sid;
    $res = update_user_info($redis,$uid,$_POST);
    echo 1; exit;
}elseif($action==2){ //  获得的所有奖品
    $prize = get_user_all_award($uid);
    $tplObj -> out['action'] = 2;
    $tplObj -> out['prize'] = $prize;
    if(!$prize){
        $tplObj -> out['is_empty'] = 1;
    }
}elseif($action==3){//获得的一个实物奖品
    $code = $_GET['code'];
    $pid = $_GET['pid'];
    //$uid.$_SESSION['USER_IMSI'].$pid;
    $res = str_decode($uid.$_SESSION['USER_IMSI'].$pid,$code);
    if($res){
        $prizename = $redis->get('rebate_draw_prize_name_'.$pid);
        if(!$prizename){
        	$prize = get_prize_info($pid);
        	$prizename = $prize['name'];
        }        
        $tplObj -> out['prizename'] = $prizename;
    }else{
        header("Location: /lottery/rebate_draw.php?sid={$sid}");
    }
    $tplObj -> out['action'] = 3;    
}
$user_data = $redis->get('rebate_draw_userinfo'.$uid);
if(!$user_data){
    $user_data = get_user_info($uid);
}
$tplObj -> out['userinfo'] = $user_data;
$tplObj -> display('rebate_draw/userinfo.html');


