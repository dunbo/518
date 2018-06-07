<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];

// 分享相关
if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
	define('SHARE_PROMOTION_HOST', 'http://test.m.anzhi.com');
	define('SHARE_M_HOST', 'http://test.m.anzhi.com');
} else {
	define('SHARE_PROMOTION_HOST', 'http://fx.anzhi.com');
	define('SHARE_M_HOST', 'http://promotion.anzhi.com');
}
$tplObj->out['share_url'] = SHARE_M_HOST . '/lottery/momsecret/index.php';
$tplObj->out['share_title'] = '那一天我发现，妈妈的手机里居然有这个……';
$tplObj->out['share_desc'] = '爱，是重复千遍，却不厌烦……专为妈妈准备的“手机使用指南”，别忘了分享给她。';

// 获得微信授权
include(dirname(realpath(__FILE__)).'/../public/WeixinShareAuth.class.php');
$wx_share_auth = new WeixinShareAuth();
$wx_share_config = $wx_share_auth->get_config();
$tplObj->out['wx_share_config'] = json_encode($wx_share_config);

// 当前时间
$now = time();

// 加载session，获得用户相关信息
session_begin();

// 活动日志
$activity_log_file = "activity_momsecret.log";

// 记日志
$log_data = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => time(),
    'key' => 'index'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display("lottery/momsecret/index.html");