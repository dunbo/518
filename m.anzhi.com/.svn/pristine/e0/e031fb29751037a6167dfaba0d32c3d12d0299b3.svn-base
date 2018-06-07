<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');

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
	define('SHARE_PROMOTION_HOST', 'http://fx.anzhi.com');
	define('SHARE_M_HOST', 'http://m.anzhi.com');
}

$tplObj->out['SHARE_PROMOTION_HOST'] = SHARE_PROMOTION_HOST;
$tplObj->out['SHARE_M_HOST'] = SHARE_M_HOST;

$tplObj->out['new_static_url'] = $configs['new_static_url'];

//活动ID
$aid = 420;
$tplObj->out['aid'] = $aid;

// 活动日志
$activity_log_file = "activity_{$aid}.log";

// 根据活动id获得软件时间
$activity_option = array(
    'where' => array(
        'id' => $aid,
        'status' => 1
    ),
    'cache_time' => 600,
    'table' => 'sj_activity'
);
$result = $model -> findOne($activity_option);
$activity_start_time = $result['start_tm'];//活动开始时间
$activity_end_time = $result['end_tm'];//活动结束时间

// 当前时间
$now = time();
$today = date('Y-m-d');

// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];
if($_SESSION['USER_IMEI']){
	$imei = $_SESSION['USER_IMEI'];
}

$telephone = trim($_POST['telephone']);

$imei_status = 0;
if(!$imei){
	$imei = '';
} else {
    $imei_status = 1;
}
$tplObj->out['sid'] = $sid;
$tplObj->out['imei_status'] = $imei_status;

// redis相关
$r_cache_time = '5184000';//redis缓存时间为2个月
$rkey_imei = "guizhou_telecom_flux:{$aid}:{$imei}";//imei的参与情况
$rkey_imei_telephone = "guizhou_telecom_flux:{$aid}:{$imei}:telephone";//imei对应的手机号码情况
$rkey_telephone = "guizhou_telecom_flux:{$aid}:{$telephone}";//telephone的参与情况
$rkey_imei_suspending = "guizhou_telecom_flux:{$aid}:{$imei}:suspending";
$rkey_telephone_suspending = "guizhou_telecom_flux:{$aid}:{$telephone}:suspending";

// 是否为新用户
$new_status = 1;
if ($imei_status) {
	if ($imei == '000000000000000') {
		$new_status = 0;
	} else {
		$act_redis = new GoRedisCacheAdapter(load_config("activation_status/redis"));
		$act_stmp = $act_redis->get('IMEI:'. $imei);
		if ($act_stmp) {
			// 不是新激活用户
			$new_status = 0;
		}
	}
}

$flux_status = 0;
if ($imei_status) {
	$flux_status = get_flux_status();
	if ($flux_status) {
		$imei_telephone = get_imei_telephone();
		$tplObj->out['imei_telephone'] = $imei_telephone;
	}
}

$tplObj->out['flux_status'] = $flux_status;

// imei参与情况
function get_flux_status() {
	global $model, $redis, $now, $r_cache_time, $imei, $rkey_imei, $rkey_imei_telephone;
	
	$flux_status = $redis->get($rkey_imei);
	if (empty($flux_status)) {
		// 尝试从mysql里读出来
		$find = get_record(array('imei' => $imei));
		if (!empty($find)) {
			// 有此imei用户
			$flux_status = 1;
			$imei_telephone = $find['telephone'];
		} else {
			$flux_status = 0;
			$imei_telephone = '';
		}
		$redis->set($rkey_imei, $flux_status, $r_cache_time);
		$redis->set($rkey_imei_telephone, $imei_telephone, $r_cache_time);
		
	}
	return $flux_status;
}

// 获得imei领取的手机号码
function get_imei_telephone() {
	global $model, $redis, $now, $r_cache_time, $imei, $rkey_imei, $rkey_imei_telephone;
	
	$imei_telephone = $redis->get($rkey_imei_telephone);
	if (empty($imei_telephone) && $imei_telephone !== '') {
		$find = get_record(array('imei' => $imei));
		if (!empty($find)) {
			$imei_telephone = $find['telephone'];
		} else {
			$imei_telephone = '';
		}
		$redis->set($rkey_imei_telephone, $imei_telephone, $r_cache_time);
	}
	return $imei_telephone;
}

// telephone参与情况
function get_telephone_status() {
	global $model, $redis, $now, $r_cache_time, $imei, $rkey_imei, $rkey_imei_telephone, $rkey_telephone, $telephone;
	
	$telephone_status = $redis->get($rkey_telephone);
	if (empty($telephone_status)) {
		// 尝试从mysql里读出来
		$find = get_record(array('telephone' => $telephone));
		if (!empty($find)) {
			$telephone_status = 1;
		} else {
			$telephone_status = 0;
		}
		$redis->set($rkey_telephone, $telephone_status, $r_cache_time);
	}
	return $telephone_status;
}

// 参与成功后入库，更新缓存
function set_flux_status() {
	global $model, $redis, $now, $r_cache_time, $imei, $rkey_imei, $rkey_imei_telephone, $rkey_telephone, $telephone;
	$data = array(
		'imei' => $imei,
		'telephone' => $telephone,
		'create_time' => $now,
		'__user_table' => 'guizhou_telecom_flux'
	);
	$model->insert($data, 'lottery/lottery');
	$redis->set($rkey_imei, 1, $r_cache_time);//设置此IMEI为已参与
	$redis->set($rkey_imei_telephone, $telephone, $r_cache_time);//设置此IMEI对应参加的手机号
	$redis->set($rkey_telephone, 1, $r_cache_time);//设置此手机号为已参与
}

function get_record($where) {
	global $model;
	$option = array(
		'where' => $where,
		'table' => 'guizhou_telecom_flux'
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if (empty($find)) {
		return false;
	}
	return $find;
}