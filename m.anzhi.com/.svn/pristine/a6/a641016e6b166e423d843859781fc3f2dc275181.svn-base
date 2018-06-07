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
define('SHARE_PROMOTION_HOST', 'http://promotion.anzhi.com');
define('SHARE_M_HOST', 'http://m.anzhi.com');

$tplObj->out['SHARE_PROMOTION_HOST'] = SHARE_PROMOTION_HOST;
$tplObj->out['SHARE_M_HOST'] = SHARE_M_HOST;

// sid
session_begin();
$sid = $_GET['sid'];
$tplObj->out['sid'] = $sid;

if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$imsi_status = 0;
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
} else {
    $imsi_status = 1;
}
$tplObj->out['imsi_status'] = $imsi_status;

// 活动id，奖品配置
$activity_option = array(
    'where' => array(
        'config_type' => 'APRILSTRIP_LOTTERY_AWARD',
        'status' => 1
    ),
    'cache_time' => 86400,
    'table' => 'pu_config'
);
$result = $model -> findOne($activity_option);
$activity_config = json_decode($result['configcontent'],true);

$aid = $activity_config['activity_id'];//活动id
$page_id = $activity_config['page_id'];//活动页面id，用来关联软件
$award_config = $activity_config['award_config'];

// 活动id
$tplObj->out['aid'] = $aid;

// 缓存key
// 缓存时间
$r_cache_time = '5184000';//redis缓存时间为两个月
// imsi打开活动最后日期与打开的总天数
$rkey_imis_open_info = "aprilstrip_{$aid}:{$imsi}:open_info";//hash类型
// imsi最近分享的日期
$rkey_imsi_last_share_day = "aprilstrip_{$aid}:{$imsi}:last_share_day";//string类型
// imsi可抽奖次数
$rkey_imsi_lottery_num = "aprilstrip_{$aid}:{$imsi}:lottery_num";//存库
// imsi已下载软件列表
$rkey_imsi_package_list = "aprilstrip_{$aid}:{$imsi}:package_list";
// imsi的奖品列表
$rkey_imsi_award_list = "aprilstrip_{$aid}:{$imsi}:award_list";//存库
// imsi是否首次访问活动首页（在首页判断，在我是王子公主页记录已访问）
$rkey_imsi_already_visit_index = "aprilstrip_{$aid}:{$imsi}:already_visit_index";

// 活动日志
$activity_log_file = "activity_{$aid}.log";

$today = date('Y-m-d');

// 中奖人名单
$option = array(
	'where' => array(
		'telephone' => array('exp', "!=''")
	),
	'order' => 'id desc',
	'cache_time' => '3600',
	'limit' => '10',
	'table' => 'aprilstrip_lottery_award'
);
$people_award_list = $model->findAll($option, 'lottery/lottery');
foreach ($people_award_list as $key => $row) {
	// 电话号码加密
	$people_award_list[$key]['telephone'] = substr_replace($row['telephone'], '****', 3, 4);
	// 获得中奖内容
	$award_level = $row['award'];
	$people_award_list[$key]['award_name'] = $award_config[$award_level - 1][1];
}
$tplObj->out['people_award_list'] = $people_award_list;




