<?php

/*
** 品牌定制活动的抽奖worker
**
*/

include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$r_cache_time = 600;

ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("brand_general_lottery", "get_num");
while ($worker->work());



// 有用户过来抽奖
function get_num($jobs) {
	global $redis;
	global $model;
    global $the_time;
    global $award_level_count;//中奖等级数量
	global $award_config;
	global $sum;
	global $r_cache_time;
	
	$redis->pingConn();
	
	$now = date('Ymd');//当天日期
	
	$now_date = date('Ymd');//当天日期
	$now_time = time();
	$now_dawn = strtotime($now_date);
	
	// 接收请求参数，json格式，应该包括imsi，aid
	$param_json = $jobs->workload();
	$param = json_decode($param_json, true);
	
	$imsi = $param['imsi'];
	$aid = $param['aid'];
	
	// 查看活动时间
	$option = array(
		'where' => array(
			'id' => $aid,
			'status' => 1
		),
		'cache_time' => '600',
		'table' => 'sj_activity'
	);
	$activity_info = $model->findOne($option);
	if (empty($activity_info)) {
		// 没有该活动
		$return_arr['code'] = -1;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	if ($now_time > $activity_info['end_tm']) {
		// 活动已结束
		$return_arr['code'] = -1;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	
	$return_arr = array();
	
	if (empty($imsi) || empty($aid)) {
		// 出错，返回没有中奖（中奖等级为0）
		$return_arr['code'] = -1;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	
	// 查看用户的可抽奖次数
	$rkey_imsi_lottery_num = "activity:brand_general_lottery:lottery_num:{$aid}:{$imsi}";
	$lottery_num = $redis->get($rkey_imsi_lottery_num);
	if (empty($lottery_num) || $lottery_num <= 0) {
		// 没有中奖次数
		$return_arr['code'] = -2;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	// 此用户的抽奖次数-1
	$lottery_num = $redis->setx('INCR', $rkey_imsi_lottery_num, -1);
	if ($lottery_num <= -1) {
		// 增加回来
		$lottery_num = $redis->setx('INCR', $rkey_imsi_lottery_num, 1);
		$return_arr['code'] = -2;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	// 对应mysql的抽奖次数-1
	$update_where = array(
		'imsi' => $imsi,
		'aid' => $aid,
	);
	$update_data = array(
		'lottery_num' => array('exp', '`lottery_num`-1'),
		'update_tm' => $now_time,
		'__user_table' => 'test_gm_lottery_num',
	);
	$ret = $model->update($update_where, $update_data, 'lottery/lottery');
	if (!$ret) {
		// 自减失败
		$lottery_num = $redis->setx('INCR', $rkey_imsi_lottery_num, 1);
		$return_arr['code'] = -3;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	
	// 扣除抽奖次数成功后，开始抽奖
	// 奖品基本信息
	$option = array(
		'where' => array(
			'aid' => $aid,
			'status' => 1,
		),
		'cache_time' => '600',
		'table' => 'test_gm_lottery_prize',
	);
	$result = $model->findAll($option, 'lottery/lottery');
	
	$award_arr = array();//奖品等级=>信息数组
	$pid_level_arr = array();//奖品pid=>等级数组
	$level_pid_arr = array();//奖品等级=>pid数组
	foreach ($result as $row) {
		$pid = $row['pid'];
		$level = $row['level'];
		$award_arr[$level] = $row;
		$pid_level_arr[$pid] = $level;
		$level_pid_arr[$level] = $pid;
	}
	
	// 今天的奖品个数缓存
	$rkey_today_award_left = "activity:brand_general_lottery:award_left:{$aid}:{$now_date}";
	$today_award_left = $redis->gethash($rkey_today_award_left);
	if (empty($today_award_left)) {
		$option = array(
			'where' => array(
				'begin_tm' => array('exp', "<={$now_time}"),
				'end_tm' => array('exp', ">={$now_time}"),
				'status' => 1,
				'aid' => $aid,
			),
			'table' => 'test_gm_probability',
		);
		$result = $model->findAll($option, 'lottery/lottery');
		foreach ($result as $row) {
			$pid = $row['pid'];
			$now_num = $row['now_num'];
			$today_probability_arr[$pid] = $row;
			
			// 换成level=>probability
			$level = $pid_level_arr[$pid];
			$today_award_left[$level] = (int)$now_num;
		}
		
		//$redis->sethash($rkey_today_award_left, $today_award_left, $r_cache_time);
		foreach ($today_award_left as $level => $now_num) {
			$redis->setx('hsetnx', $rkey_today_award_left, $level, $now_num);
		}
		$redis->setx('expire', $rkey_today_award_left, $r_cache_time);
	}
	
	// 今天的奖品概率缓存
	$rkey_today_probability = "activity:brand_general_lottery:probability:{$aid}:{$now_date}";
	$today_level_probability = $redis->gethash($rkey_today_probability);
	if (empty($today_level_probability)) {
		// 奖品当天概率
		$option = array(
			'where' => array(
				'begin_tm' => array('exp', "<={$now_time}"),
				'end_tm' => array('exp', ">={$now_time}"),
				'status' => 1,
				'aid' => $aid,
			),
			'table' => 'test_gm_probability',
		);
		$result = $model->findAll($option, 'lottery/lottery');
		$today_probability_arr = array();
		$today_level_probability = array();
		foreach ($result as $row) {
			$pid = $row['pid'];
			$probability = $row['probability'];
			$today_probability_arr[$pid] = $row;
			
			// 换成level=>probability
			$level = $pid_level_arr[$pid];
			$today_level_probability[$level] = $probability;
		}
		// 将$today_level_probability写redis
		if (!empty($today_level_probability)) {
			ksort($today_level_probability, SORT_NUMERIC);
			foreach ($today_level_probability as $level => $probability) {
				$redis->setx('hsetnx', $rkey_today_probability, $level, $probability);
			}
			$ret = $redis->setx('expire', $rkey_today_probability, $r_cache_time);
		}
	}
	// 根据概率取base
	$rkey_lottery_base = "activity:brand_general_lottery:base:{$aid}:{$now_date}:" . md5(json_encode($today_level_probability));
	$today_level_base = $redis->gethash($rkey_lottery_base);
	$multiple = $today_level_base['multiple'];
	$base_arr = $today_level_base['base_arr'];
	if (empty($multiple) || empty($base_arr)) {
		$today_level_base = get_base($today_level_probability);
		$multiple = $today_level_base['multiple'];
		$base_arr = $today_level_base['base_arr'];
		$redis->sethash($rkey_lottery_base, $today_level_base, $r_cache_time);
	}
	
	// 根据概率抽奖
	$award_level = get_award($base_arr, $multiple - 1);
	
	if ($award_level <= 0) {
		// 没有中奖
		$return_arr['code'] = 0;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	
	// 中奖类型
	$award_type = $award_arr[$award_level]['type'];
	if ($award_type != 1) {
		// 礼包奖
		$gift_info = array();
		$gift_package = $award_arr[$award_level]['gift_package'];
		$gift_info['gift_path'] = $award_arr[$award_level]['gift_path'];
		$gift_info['gift_package'] = $gift_package;
		if ($award_type == 2) {
			// 礼包奖且有礼包码
			$rkey_gift_list = "activity:brand_general_lottery:gift_list:{$aid}:{$gift_package}";
			$gift_no = $redis->rpop($rkey_gift_list);
			if (empty($gift_no)) {
				// 没有礼包码了，还是没有中奖
				$return_arr['code'] = 0;
				$data = array();
				$data['award_level']= 0;
				$return_arr['data'] = $data;
				return json_encode($return_arr);
			}
			// 补充信息
			$gift_info['gift_no'] = $gift_no;
		} else if ($award_type == 3) {
			// 礼包奖但无礼包码
		}
	}
	
	// 中奖了，查看一下redis中此奖品个数是否大于0
	$left = $redis->gethash($rkey_today_award_left, $award_level);
	if (!is_int($left) || $left <= 0) {
		$return_arr['code'] = 0;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	
	// 奖品大于0，redis今天此奖品的数量自减1
	$left = $redis->setx('HINCRBY', $rkey_today_award_left, $award_level, -1);
	if (!is_int($left)) {
		// todo，返回不是数字
		// 自减出错
		$return_arr['code'] = 0;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	// 自减出来是负数，还是没有奖品
	if ($left < 0) {
		$return_arr['code'] = 0;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	
	// redis自减后>=0，mysql中的now_num自减
	$award_pid = $level_pid_arr[$award_level];
	$update_where = array(
		'pid' => $award_pid,
		'end_tm' => array('exp', ">={$now_time}"),
		'status' => 1,
		'aid' => $aid,
	);
	$update_data = array(
		'now_num' => array('exp', '`now_num`-1'),
		'update_tm' => $now_time,
		'__user_table' => 'test_gm_probability',
	);
	$ret = $model->update($update_where, $update_data, 'lottery/lottery');
	
	// mysql自减失败，还是没有中奖？
	if (!$ret) {
		$return_arr['code'] = 0;
		$data = array();
		$data['award_level']= 0;
		$return_arr['data'] = $data;
		return json_encode($return_arr);
	}
	
	// 返回用户中奖信息
	$return_arr['code'] = 0;
	$data = array();
	$data['award_level']= $award_level;
	if (!empty($gift_info)) {
		$data = array_merge($data, $gift_info);
	}
	$return_arr['data'] = $data;
	return json_encode($return_arr);
}

// 根据抽奖概率得抽奖base
function get_base($arr) {
	$multiple = 1;
	$arr_transit_arr = array();
	$denominator_arr = array();
	foreach ($arr as $k => $v) {
		$tmp = explode('/', $v);
		$arr_transit_arr[$k][0] = $tmp[0];
		$arr_transit_arr[$k][1] = $tmp[1];
		$denominator = $tmp[1];
		if (!array_key_exists($denominator, $denominator_arr)) {
			$multiple *= get_least_common_multiple($multiple, $denominator);
		}
		$denominator_arr[$denominator] = 1;
	}
	if ($multiple == 0) {
		// 出错，返回没有中奖
		return 0;
	}
	$base_arr = array();
	foreach ($arr as $k => $v) {
		$numerator = $arr_transit_arr[$k][0];
		$denominator = $arr_transit_arr[$k][1];
		$base_arr[$k] = ($numerator*$multiple)/$denominator;
	}
	$data = array(
		'multiple' => $multiple,
		'base_arr' => $base_arr
	);
	return $data;
}

// 抽奖程序
function get_award($arr, $base) {
	$start = 0;
	$map = array();
	foreach ($arr as $k => $v) {
		$map[$k] = $start+$v;
		$start += $v;
	}
	$r = mt_rand(0, $base);
	foreach ($map as $k => $v) {
		if ($r < $v) {
			return $k;
		}
	}
	return 0;
}

// 获得最小公倍数
function get_least_common_multiple($a, $b) {
	$max = max($a, $b);
	$min = min($a, $b);
	if ($min <= 0 || $max <= 0) {
		return 0;
	}
	for ($i = 1; $i <= $max*$min; $i++) {
		if (is_int($max*$i/$min)) {
			return $max*$i;
		}
	}
	return 0;
}