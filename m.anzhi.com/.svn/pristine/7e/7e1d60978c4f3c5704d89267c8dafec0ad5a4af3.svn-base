<?php
/**
 * @desc 超级碗投票软件已有投票数
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
$package = explode(',',$_GET['package']);
$softid = explode(',',$_GET['softid']);
$res = array();
//permanentlog('activity_'.$aid.'.log',print_r($package,true));
$vote_app_num = "super_bowl_active_{$aid}_vote_app:";//软件投票数
foreach($package as $k=>$v){
    $num =  $redis->gethash($vote_app_num,$v);
    $res[$k]['softid'] = $softid[$k];
    $res[$k]['package'] = $v;
    $res[$k]['num']  = $num['num'];
}
//permanentlog('activity_'.$aid.'.log',print_r($res,true));
echo json_encode($res);
return json_encode($res);
