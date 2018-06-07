<?php
/*
 * 超级碗分享页
 */
include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once (dirname(realpath(__FILE__)).'/superbowl_fun.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}
session_begin();
$aid = $_GET['aid'];
if($_SESSION['USER_IMSI']){
    $imsi = $_SESSION['USER_IMSI'];
}

$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'share_soft'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

$rkey_imsi_lottery_num = "superbowl_{$aid}_{$imsi}_lottery_num";//用户可抽奖次数
$share_imsi = "superbowl_lottery:share_{$imsi}_{$aid}";
$share_must = "superbowl_lottery:share_must_{$aid}";
$model = new GoModel();
$activity_option = array(
    'where' => array(
        'id' => $aid
    ),
    'cache_time' => 300,
    'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);

$page_option = array(
    'where' => array(
        'ap_id' => $activity_result['activity_page_id']
    ),
    'cache_time' => 300,
    'table' => 'sj_activity_page',
    'field' => 'must_share,share_add'
);
$page_result = $model -> findOne($page_option);
if($page_result['must_share'] == 1){
    $my_share = $redis -> gethash($share_must);
    if(!$my_share[$imsi]){
        $redis -> sethash($share_must,array($imsi => 1),86400*30);
    }
}

$today = date('Ymd');

if($page_result['share_add'] == 1 && $imsi){
    $my_share = $redis -> gethash($share_imsi,$today);
    if($my_share){
       // echo 1;
        echo 200;
        return 200;
    }else{
        $now_num = $redis -> setx('incr',$rkey_imsi_lottery_num,1);
        setLotteryNum($now_num);
        $redis -> sethash($share_imsi,array($today => 1),86400*30);
        echo $now_num;
        return $now_num;
    }
}else{
    //echo 2;
    echo 200;
    return 200;
}
