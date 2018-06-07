<?php
/*
** 初始页
*/
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model	=	new GoModel();

// cdn资源地址
$tplObj -> out['static_url']		=	$configs['new_static_url'];
$tplObj -> out['new_static_url']	=	$configs['static_url'];

//获取host
$activity_host = $configs['activity_url'];
// 当前时间
$now = time();
$today = date('Y-m-d');

// 加载session，获得用户相关信息
session_begin();
if( $_SESSION['USER_IMSI'] && $_SESSION['USER_IMSI'] != '000000000000000' ) {
	$imsi = $_SESSION['USER_IMSI'];
}else {
	$imsi = '';
}

if($_SESSION['VERSION_CODE']) {
	$is_version = 1;
}else {
	$is_version = 0;
}

$prefix		=	"aug_h5";
$aid = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$is_share	=	$_GET['is_share'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($aid)){
	exit;
}
$tplObj->out['aid']			=	$aid;
$tplObj->out['sid']			=	$sid;
$tplObj->out['imsi']		=	$imsi;
$tplObj->out['prefix']		=	$prefix;
$tplObj->out['is_share']	=	$is_share;
$tplObj->out['is_version']	=	$is_version;
//获取host
$tplObj -> out['activity_host']	=	$configs['activity_url'];
$tplObj -> out['is_test']		=	$configs['is_test'];

 if($_GET['test']){
	$_SESSION['VERSION_CODE'] = 6400;
	$imsi = 1233;
 }
// $imsi = 1233;
// 活动id
// 根据活动id获得软件页面id（主要用在活动页面下载软件，判断下载的软件是否有效）
$activity_option = array(
    'where' => array(
        'id'		=>	$aid,
        'status'	=>	1
    ),
    'cache_time'	=>	600,
    'table'			=>	'sj_activity'
);
$result = $model -> findOne($activity_option);

// 活动日志
$activity_log_file = "activity_{$aid}.log";

$activity_start_time = $result['start_tm'];//活动开始时间
$activity_end_time = $result['end_tm'];//活动结束时间
$activity_end_url = $result['end_url'];//活动结束页


