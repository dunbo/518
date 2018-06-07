<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$aid = 189;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
    $sid = $_GET['sid'];
	session_id($_GET['sid']);
    $tplObj->out['sid'] = $_GET['sid'];
}

session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
    $tplObj->out['imsi_status'] = 0;
} else {
    $tplObj->out['imsi_status'] = 1;
}

// 页面请求url时会用到
$tplObj->out['sid'] = $_GET['sid'];

//var_dump($sid, $imsi);

// 缓存key
// imsi签到信息
$rkey_imsi_sign_info = "spring_{$aid}:{$imsi}:sign_info";//存库
// imsi当天是否分享过
$rkey_imsi_ever_share = "spring_{$aid}:{$imsi}:ever_share";
// imsi可抽奖次数
$rkey_imsi_lottery_num = "spring_{$aid}:{$imsi}:lottery_num";//存库
// imsi已下载软件列表
$rkey_imsi_package_list = "spring_{$aid}:{$imsi}:package_list";
// imsi的奖品列表
$rkey_imsi_award_list = "spring_{$aid}:{$imsi}:award_list";//存库


// 奖品配置
$award_level_option = array(
    'where' => array(
        'config_type' => 'SPRING_LOTTERY_AWARD',
        'status' => 1
    ),
    'cache_time' => 86400,
    'table' => 'pu_config'
);
$award_level_result = $model -> findOne($award_level_option);
$award_config = json_decode($award_level_result['configcontent'],true);

// 最近大家中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'limit' => 10,
	'cache_time' => 600,
	'table' => 'spring_lottery_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

if (!empty($all_award_result)) {
    // 处理一下上面的中奖信息
    foreach ($all_award_result as $key => $val) {
        $all_award_result[$key]['award'] = $award_config[$val['award']-1][1];
        $all_award_result[$key]['the_time'] = date('Y-m-d',$val['time']);
		$all_award_result[$key]['telephone'] = substr_replace($val['telephone'],'****',3,4);
    }
}
$tplObj -> out['all_award_result'] = $all_award_result;
$tplObj -> out['all_award_count'] = count($all_award_result);

$tplObj->out['aid'] = $aid;

// 活动日志
$activity_log_file = "activity_{$aid}.log";