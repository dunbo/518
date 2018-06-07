<?php
include_once(dirname(realpath(__FILE__)).'/../../init.php');

$aid = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
if(!ctype_digit($aid)){
	echo '活动id无效';
	exit;
}
$tplObj->out['aid'] = $aid;
// 活动日志
$activity_log_file = "activity_{$aid}.log";

list($redis, $model) = load_config_redis();
// cdn资源地址
$tplObj->out['is_test'] = $configs['is_test'];
$tplObj->out['new_static_url'] = $configs['new_static_url'];
$tplObj->out['activity_host'] = $configs['activity_url'];
$tplObj->out['is_share'] = $_GET['is_share'];

// 当前时间
$now = time();
$prefix = 'dati';
$tplObj->out['prefix'] = $prefix;

// 加载session，获取用户相关信息
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$tplObj->out['sid'] = $sid;
$tplObj->out['version_code'] = $_SESSION['VERSION_CODE'] ? $_SESSION['VERSION_CODE'] : '';
if ($_SESSION['USER_IMSI']) {
    $imsi = $_SESSION['USER_IMSI'];
}else{
	$imsi = '';
}

if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
    $tplObj->out['title'] = '安智答题助手';
    $tplObj->display("lottery/download_for_flow/ios.html");die;
}

if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
	$tplObj->out['title'] = '安智答题助手';
    $tplObj->display("lottery/download_for_flow/weixin.html");die;
}