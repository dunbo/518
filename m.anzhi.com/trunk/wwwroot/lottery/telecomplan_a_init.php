<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();

// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];

// 用户累计可发送次数最大值
define('TELECOMPLAN_A_MAX_TOTAL_TIMES', 4);

session_begin();

$sid = $_GET['sid'];
$tplObj->out['sid'] = $sid;

$imei = strtoupper(trim($_SESSION['USER_IMEI']));
$imsi = $_SESSION['USER_IMSI'];

$aid = 360;
$tplObj->out['aid'] = $aid;
$now = time();

if (!$imei || $imei == '000000000000000') {
	$imei_status = 0;
	$tplObj -> out['imei_status'] = $imei_status;
} else {
	$imei_status = 1;
}

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

// redis相关key
$rkey_imei_times = "telecomplan_a:{$aid}:{$imei}:times";
$rkey_imei_total_times = "telecomplan_a:{$aid}:{$imei}:total_times";
$rkey_imei_package_list = "telecomplan_a:{$aid}:{$imei}:package_list";

// 检查此用户是否有可发送次数
if ($imei_status) {
	$init_status = init_user();
	$times = get_num();
} else {
	$times = 0;
}
$tplObj->out['times'] = $times;

function init_user() {
	global $model, $redis, $rkey_imei_times, $rkey_imei_total_times, $imei;
	
	$now = time();
	$times = $redis->get($rkey_imei_times);
	$total_times = $redis->get($rkey_imei_total_times);
	
	$condition1 = (empty($times) && $times !== 0);
	$condition2 = (empty($total_times) && $total_times !== 0);
	
	if ($condition1 || $condition2) {
		// 尝试从mysql中读出来
		$option = array(
			'where' => array(
				'imei' => $imei,
			),
			'table' => 'telecomplan_a_imei',
		);
		$info = $model->findOne($option, 'lottery/lottery');
		if (!$info) {
			// 没有此用户数据，更新mysql、更新缓存
			$data = array(
				'imei' => $imei,
				'total_times' => 0,
				'times' => 0,
				'create_time' => $now,
				'update_time' => $now,
				'__user_table' => 'telecomplan_a_imei',
			);
			$ret = $model->insert($data, 'lottery/lottery');
			if (!$ret) {
				// 用户初始化失败
				return false;
			}
			$times = 0;
			$total_times = 0;
		} else {
			$times = (int)$info['times'];
			$total_times = (int)$info['total_times'];
		}
		$redis->setnx($rkey_imei_times, $times);
		$redis->setnx($rkey_imei_total_times, $total_times);
	}
	return true;
}

function get_num() {
	global $model, $redis, $rkey_imei_times, $imei;
	
	$now = time();
	// 获得可发送次数
	$times = $redis->get($rkey_imei_times);
	if (empty($times) && $times !== 0) {
		// 尝试从mysql中读出来
		$option = array(
			'where' => array(
				'imei' => $imei,
			),
			'table' => 'telecomplan_a_imei',
		);
		$info = $model->findOne($option, 'lottery/lottery');
		if (!$info) {
			// 没有此用户，报错
			return false;
		} else {
			$times = (int)$info['times'];
		}
		$redis->setnx($rkey_imei_times, $times);
	}
	return $times;
}

// 累计可抽奖次数+1
function increase_total_times_by1() {
	global $model, $redis, $rkey_imei_total_times, $imei;
	
	// 缓存、mysql都自增1
	$total_times = $redis -> setx('incr',$rkey_imei_total_times, 1);
	if (!is_int($total_times)) {
		// 出错
		return false;
	}
	if ($total_times > TELECOMPLAN_A_MAX_TOTAL_TIMES) {
		// 超出四次最大次数，还原回4，并返回
		$redis->set($rkey_imei_total_times, TELECOMPLAN_A_MAX_TOTAL_TIMES);
		return false;
	}
	$now = time();
	$update_where = array(
		'imei' => $imei,
	);
	$update_data = array(
		'total_times' => array('exp', '`total_times`+1'),
		'update_time' => $now,
		'__user_table' => 'telecomplan_a_imei'
	);
	$model->update($update_where, $update_data, 'lottery/lottery');
	return $total_times;
}

// 可抽奖次数+1
function increase_times_by1() {
	global $model, $redis, $rkey_imei_times, $imei;

	// 累计次数+1
	$ret = increase_total_times_by1($imei);
	if ($ret === false) {
		return false;
	}
	// 缓存、mysql都自增1
	$times = $redis -> setx('incr',$rkey_imei_times, 1);
	if (!is_int($times)) {
		// 出错
		return false;
	}
	$update_where = array(
		'imei' => $imei,
	);
	$now = time();
	$update_data = array(
		'times' => array('exp', '`times`+1'),
		'update_time' => $now,
		'__user_table' => 'telecomplan_a_imei'
	);
	$model->update($update_where, $update_data, 'lottery/lottery');
	return $times;
}

// 可抽奖次数-1
function decrease_times_by1() {
	global $model, $redis, $rkey_imei_times, $imei;
	
	// 缓存、mysql都自减1
	$times = $redis -> setx('incr',$rkey_imei_times, -1);
	if (!is_int($times)) {
		// 出错
		return false;
	}
	if ($times < 0) {
		// 还原回0，并返回
		$redis->set($rkey_imei_times, 0);
		return false;
	}
	$update_where = array(
		'imei' => $imei,
	);
	$now = time();
	$update_data = array(
		'times' => array('exp', '`times`-1'),
		'update_time' => $now,
		'__user_table' => 'telecomplan_a_imei'
	);
	$model->update($update_where, $update_data, 'lottery/lottery');
	return $times;
}