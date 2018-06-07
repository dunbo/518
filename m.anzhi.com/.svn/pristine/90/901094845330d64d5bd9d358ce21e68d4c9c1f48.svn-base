<?php
/*
** 福利初始页
*/

include_once(dirname(realpath(__FILE__)).'/../../init.php');

$config = load_config('lottery_cache/redis', "lottery");
if($config){
	$redis = new GoRedisCacheAdapter($config);
}else{
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

/*echo '<pre>';
print_r($redis->getx('keys', 'welfare:*:receive:num'));
echo '</pre>';*/

// 静态资源地址
$tplObj->out['new_static_url'] = $configs['new_static_url'];
$tplObj->out['activity_share_url'] = $configs['activity_share_url'];
// 图片地址
$tplObj->out['imgurl'] = getImageHost();
//是否来自分享
$tplObj->out['is_share'] = $_GET['is_share'];
//福利日志
$welfare_log_file = "welfare.log";

// 加载session，获取用户相关信息
session_begin();
if (!$_GET['sid']) $_GET['sid'] = session_id();
$sid = $_GET['sid'];
$tplObj->out['sid'] = $sid;
if($_SESSION['USER_IMSI']){
    $imsi = $_SESSION['USER_IMSI'];
}else{
	$imis = '';
}

//判断是否微信浏览器
if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
	$tplObj->out['is_weixin'] = 1;
}