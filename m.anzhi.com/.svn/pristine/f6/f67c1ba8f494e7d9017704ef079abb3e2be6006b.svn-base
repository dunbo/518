<?php
/**
 * @desc 超级碗投票
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
if(!$_GET['softid']||!$_GET['softname']||!$_GET['package']||!$_GET['category']){
//    permanentlog('activity_'.$aid.'.log', print_r($_GET['softid'],true));
//    permanentlog('activity_'.$aid.'.log', print_r($_GET['softname'],true));
//    permanentlog('activity_'.$aid.'.log', print_r($_GET['package'],true));
//    permanentlog('activity_'.$aid.'.log', print_r($_GET['category'],true));
    echo 200;
    return 200;
}
$softid = explode(',',$_GET['softid']);
$softname = explode(',',$_GET['softname']);
$package = explode(',',$_GET['package']);
$category = explode(',',$_GET['category']);
foreach($package as $k=>$v){
    $log_data = array(
        'imsi' => $imsi,
        'device_id' => $_SESSION['DEVICEID'],
        'activity_id' => $aid,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'sid' => $_GET['sid'],
        'time' => time(),
        'package'=>$v,
        'key' => 'vote_app'
    );
    permanentlog('activity_'.$aid.'.log', json_encode($log_data));
}


$rkey_imsi_lottery_num = "superbowl_{$aid}_{$imsi}_lottery_num";//用户可抽奖次数
$vote_imsi = "superbowl_lottery:vote_{$imsi}_{$aid}"; //投票
$vote_app_num = "super_bowl_active_{$aid}_vote_app:";//软件投票数
$model = new GoModel();
$today = date('Ymd');
//$redis->delete($vote_imsi);
$my_vote = $redis -> gethash($vote_imsi,$today);
if($my_vote){
    //今天已经投过票
    echo 200;
    return 200;
}else{
    //设置可抽奖数加1
    $now_num = $redis -> setx('incr',$rkey_imsi_lottery_num,1);
    setLotteryNum($now_num);
    //投票软件入库
    $icon = explode(',',$_GET['icon']);
    saveVoteApp($softid,$softname,$package,$category,$icon);
    //设置今天已投票
    $redis -> sethash($vote_imsi,array($today => 1),86400*30);
    echo $now_num;
    return $now_num;
}