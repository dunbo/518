<?php
/*
** 初始页
*/
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();

// 常量
if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
	define('SHARE_PROMOTION_HOST', 'http://118.26.203.23');
	define('SHARE_M_HOST', 'http://118.26.203.23');
} else {
	define('SHARE_PROMOTION_HOST', 'http://promotion.anzhi.com');
	define('SHARE_M_HOST', 'http://m.anzhi.com');
}

$tplObj->out['SHARE_PROMOTION_HOST'] = SHARE_PROMOTION_HOST;
$tplObj->out['SHARE_M_HOST'] = SHARE_M_HOST;

// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];

// 活动id
$aid = 374;

// 分享地址
$tplObj->out['promotion_share_url'] = SHARE_PROMOTION_HOST . "/a_{$aid}.html";

// 当前时间
$now = time();
$today = date('Y-m-d');

// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];
$tplObj->out['sid'] = $sid;

$forward_arg = array();
foreach ($_GET as $k => $v) {
	$forward_arg[$k] = $v;
}
unset($forward_arg['aid']);

$query_str = '';
foreach ($forward_arg as $k => $v) {
	if ($query_str != '') {
		$query_str .= '&';
	}
	$query_str .= "{$k}={$v}";
}

// 各个活动配置
$time1 = '2015-12-3 00:00:00';//奔跑吧
$time2 = '2015-12-10 00:00:00';//消灭星星
$time3 = '2015-12-16 00:00:00';//QQ阅读活动
$time4 = '2015-12-21 00:00:00';//活捉
$time5 = '2015-12-23 00:00:00';//圣诞
$time6 = '2015-12-30 00:00:00';//元旦

$end_time1 = '2015-12-6 23:59:59';
$end_time2 = '2015-12-13 23:59:59';
$end_time3 = '2015-12-20 23:59:59';
$end_time4 = '2015-12-22 23:59:59';
$end_time5 = '2015-12-27 23:59:59';
$end_time6 = '2016-1-10 23:59:59';

$aid1 = 371;
$aid2 = 0;
$aid3 = 0;
$aid4 = 0;
$aid5 = 0;
$aid6 = 0;

$url1 = "http://promotion.anzhi.com/lottery/double11_index.php?{$query_str}&aid={$aid1}";
$url2 = "http://promotion.anzhi.com/lottery/activity_guide.php?{$query_str}&aid={$aid2}";
$url3 = "http://promotion.anzhi.com/lottery/double11_index.php?{$query_str}&aid={$aid3}";
$url4 = "http://promotion.anzhi.com/lottery/double11_index.php?{$query_str}&aid={$aid4}";
$url5 = "http://promotion.anzhi.com/lottery/double11_index.php?{$query_str}&aid={$aid5}";
$url6 = "http://promotion.anzhi.com/lottery/double11_index.php?{$query_str}&aid={$aid6}";

$tplObj->out['time1'] = strtotime($time1);
$tplObj->out['time2'] = strtotime($time2);
$tplObj->out['time3'] = strtotime($time3);
$tplObj->out['time4'] = strtotime($time4);
$tplObj->out['time5'] = strtotime($time5);
$tplObj->out['time6'] = strtotime($time6);

$tplObj->out['end_time1'] = strtotime($end_time1);
$tplObj->out['end_time2'] = strtotime($end_time2);
$tplObj->out['end_time3'] = strtotime($end_time3);
$tplObj->out['end_time4'] = strtotime($end_time4);
$tplObj->out['end_time5'] = strtotime($end_time5);
$tplObj->out['end_time6'] = strtotime($end_time6);

$tplObj->out['url1'] = $url1;
$tplObj->out['url2'] = $url2;
$tplObj->out['url3'] = $url3;
$tplObj->out['url4'] = $url4;
$tplObj->out['url5'] = $url5;
$tplObj->out['url6'] = $url6;

$tplObj->out['now'] = $now;

$tplObj->display('lottery/activity_guide.html');
exit;