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

session_begin();
$active_id=223;
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
    $tplObj -> out['username'] = $_SESSION['USER_NAME'];
    $tplObj -> out['is_login'] = 1;
    $tplObj -> out['uid'] = $uid;
}else{//未登录
    $lottery_num = 0;
    $tplObj -> out['is_login'] = 2;
}
$model = new GoModel();
$prize = getUserAward($redis,$model,100);

$tplObj -> out['sid'] =  $sid;
$tplObj -> out['prize'] = $prize;
$tplObj -> display('rebate_draw/end.html');


