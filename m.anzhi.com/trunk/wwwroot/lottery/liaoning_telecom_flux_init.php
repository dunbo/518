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
$act_redis = new GoRedisCacheAdapter(load_config("activation_status/redis"));

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

// 当前时间
$now = time();
$today = date('Y-m-d');

// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];

if($_SESSION['USER_IMEI']){
	$imei = strtoupper(trim($_SESSION['USER_IMEI']));
}

$imei_status = 0;
if(!$imei){
	$imei = '';
} else {
    $imei_status = 1;
}
$tplObj->out['sid'] = $sid;
$tplObj->out['imei_status'] = $imei_status;

$aid = 370;

// 活动id
$tplObj->out['aid'] = $aid;

// 根据活动id获得软件页面id（主要用在活动页面下载软件，判断下载的软件是否有效）
$activity_option = array(
    'where' => array(
        'id' => $aid,
        'status' => 1
    ),
    'cache_time' => 600,
    'table' => 'sj_activity'
);
$result = $model -> findOne($activity_option);
$page_id = $result['activity_page_id'];//活动页面id，用来关联软件
$activity_start_time = $result['start_tm'];//活动开始时间
$activity_end_time = $result['end_tm'];//活动结束时间

// redis相关
$r_cache_time = '5184000';//redis缓存时间为2个月
$rkey_imei = "liaoning_telecom_flux:{$aid}:{$imei}";//imei的参与情况
$rkey_imei_telephone = "liaoning_telecom_flux:{$aid}:{$imei}:telephone";//imei对应的手机号码情况

// 活动日志
$activity_log_file = "activity_{$aid}.log";

// 判断活动是否结束
$end_status = 0;
if ($now > $activity_end_time) {
	$end_status = 1;
}
$tplObj->out['end_status'] = $end_status;

// 判断用户是否为新激活用户
$new_status = 1;
if ($imei_status) {
	if ($imei == '000000000000000') {
		$new_status = 0;
	} else {
		$act_stmp = $act_redis->get('IMEI:'. $imei);
		if ($act_stmp) {
			// 不是新激活用户
			$new_status = 0;
		}
	}
}
$tplObj->out['new_status'] = $new_status;

// 如果session有效且是新激活用户
$get_flux_status = 0;
if ($imei_status && $new_status) {
	// 判断此用户是否已参加过
	$times = check_get_flux_status();
	if ($times) {
		$get_flux_status = 1;
		$telephone = get_imei_telephone();
		$tplObj->out['telephone'] = $telephone;
	}
}
$tplObj->out['get_flux_status'] = $get_flux_status;


function check_get_flux_status() {
	global $redis, $model, $aid, $imei, $r_cache_time, $rkey_imei, $rkey_imei_telephone;
	
	$times = $redis->get($rkey_imei);
	if (empty($times) && $times !== 0) {
		// 从mysql中读出来
		$data = array(
			'where' => array(
				'imei' => $imei,
			),
			'table' => 'liaoning_telecom_flux'
		);
		$find = $model->findOne($data, 'lottery/lottery');
		if ($find) {
			// 更新hash
			$times = 1;
			$telephone = $find['telephone'];
			$rkey_telephone = "liaoning_telecom_flux:{$aid}:{$telephone}";
			$redis->setnx($rkey_imei, 1);
			$redis->expire($rkey_imei, $r_cache_time);
			$redis->setnx($rkey_telephone, 1);
			$redis->expire($rkey_telephone, $r_cache_time);
			$redis->setnx($rkey_imei_telephone, $telephone);
			$redis->expire($rkey_imei_telephone, $r_cache_time);
		} else {
			// 表里没有，设置hash
			$$times = 0;
			$redis->setnx($rkey_imei, 0);
			$redis->expire($rkey_imei, $r_cache_time);
		}
	}
	return $times;
}

function get_imei_telephone() {
	global $redis, $model, $aid, $imei, $r_cache_time, $rkey_imei, $rkey_imei_telephone;
	
	$telephone = $redis->get($rkey_imei_telephone);
	if (empty($telephone)) {
		$data = array(
			'where' => array(
				'imei' => $imei,
			),
			'table' => 'liaoning_telecom_flux'
		);
		$find = $model->findOne($data, 'lottery/lottery');
		if ($find) {
			$telephone = $find['telephone'];
			$redis->set($rkey_imei_telephone, $telephone, $r_cache_time);
		}
	}
	$telephone = empty($telephone)? '' : $telephone;
	return $telephone;
}

