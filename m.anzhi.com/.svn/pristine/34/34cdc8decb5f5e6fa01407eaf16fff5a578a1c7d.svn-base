<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
$app_key = "1517468107oN85pyrajO1IS4dgg6hJ";
$app_secret = "JVcEyysUVSagC0413YkrA697";
$cache_prefix = 'PREORDER:';
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

# 指定session_id
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$sid = session_id();

function generate_order_id() {
	list($msec, $sec) = explode(' ', microtime());
	$time = date('YmdHis', $sec) . sprintf('%06d', intval($msec * 1000000));
	$suffix = sprintf('%03d',mt_rand(0,999));
	$order_id = "{$time}{$suffix}";
	return $order_id;
}

function get_success_order($uid) {
	global $model;
	# 获取所有有效礼券
	$option = array(
		'table' => 'preorder_coupon',
		'where' => array(
			'status' => 1,
		),
		'field' => '*',
		'order' => 'rank',
	);
	$res = $model->findAll($option, 'lottery/lottery');
	$coupon = array();
	foreach ($res as $v) {
		$coupon[$v['id']] = $v;
	}

	# 获取用户礼券购买记录
	$option = array(
		'table' => 'preorder_user_order',
		'where' => array(
			'user_id' => $uid,
			'status' => 1,
		),
	);	
	$res = $model->findAll($option, 'lottery/lottery');
	$record = array();
	foreach ($res as $v) {
		$order_coupons = json_decode($v['coupon_set'], true);
		foreach ($order_coupons as $coupon_id => $num) {
			$record[] = array(
				'name' => $coupon[$coupon_id]['coupon_name'],
				'amount' => $num,
				'pay_time' => date('Y-m-d H:i:s', $v['pay_time']),
				'rank' => $coupon[$coupon_id]['rank'],
			);
		}
	}
	usort($record, function($a,$b) {
		if ($a['pay_time'] < $b['pay_time'])
			return 1;
		elseif ($a['pay_time'] > $b['pay_time'])
			return -1;
		else 
			if ($a['rank'] > $b['rank'])
		 		return 1;
			elseif ($a['rank'] < $b['rank'])
				return -1;
			else 
				return 0;
	});
	return $record;
}

function get_unpaid_order($uid) {
	global $model;
	# 获取所有有效礼券
	$option = array(
		'table' => 'preorder_coupon',
		'where' => array(
			'status' => 1,
		),
		'field' => '*',
		'order' => 'rank',
	);
	$res = $model->findAll($option, 'lottery/lottery');
	$coupon = array();
	foreach ($res as $v) {
		$coupon[$v['id']] = $v;
	}

	# 获取用户礼券购买记录
	$option = array(
		'table' => 'preorder_user_order',
		'where' => array(
			'user_id' => $uid,
			'status' => 0,
			'expire_time' => array('exp', '>(' . time() . '+60)'),
		),
	);	
	$res = $model->findAll($option, 'lottery/lottery');
	$orders = array();
	foreach ($res as $v) {
		$price = 0;
		$real_price = 0;
		$order_coupons = json_decode($v['coupon_set'], true);
		$tmp = array();
		foreach ($order_coupons as $coupon_id => $num) {
			$tmp[] = array(
				'name' => $coupon[$coupon_id]['coupon_name'],
				'amount' => $num,
				'price' => $coupon[$coupon_id]['coupon_price'],
				'real_price' => $coupon[$coupon_id]['coupon_real_price'],
			);
			$price += $coupon[$coupon_id]['coupon_price'] * $num;
			$real_price += $coupon[$coupon_id]['coupon_real_price'] * $num;
		}
		$v['coupon'] = $tmp;
		$v['price'] = $price;
		$v['real_price'] = $real_price;
		$v['expire_time'] -= 60;
		$orders[] = $v;
	}
	return $orders;
}

function get_unpaid_order_num($uid) {
	global $model;

	# 获取待支付礼券数量
	$option = array(
		'table' => 'preorder_user_order',
		'where' => array(
			'user_id' => $uid,
			'status' => 0,
			'expire_time' => array('exp', '>(' . time() . '+60)'),
		),
		'field' => 'count(*) c',
	);	
	$res = $model->findOne($option, 'lottery/lottery');
	return empty($res['c']) ? 0 : $res['c'];
}

function get_coupon_remaining_amount($coupon_id) {
	global $cache_prefix;
	global $redis;
	$keys = array();
	$prefix = $cache_prefix . 'COUPON:';
	foreach ($coupon_id as $v)
		$keys[] = $prefix . $v;
	$res = $redis->gets($keys);
	$amount = array();
	foreach ($coupon_id as $v)
		$amount[$v] = empty($res[$prefix . $v]) ? 0 : $res[$prefix . $v];
	return $amount;
}

function get_user_coupon_amount($uid) {
	global $cache_prefix;
	global $redis;
	$key = $cache_prefix . 'USER_COUPON:' . $uid;
	$res = $redis->gethash($key);
	return $res;
}

function adjust_coupon_amount($uid, $data) {
	global $redis;
	global $cache_prefix;
	$redis->pingConn();
	$redis->setx('WATCH', $cache_prefix . 'USER_COUPON:' . $uid);
	$coupon_id = array();
	foreach ($data as $v) {
		$coupon_id[] = $v['id'];
	}
	$user_coupon = get_user_coupon_amount($uid);
	$remaining_coupon = get_coupon_remaining_amount($coupon_id);
	$coupon_to_write = array();
	$user_coupon_to_write = array();
	foreach ($data as $v) {
		if ($v['num'] > $remaining_coupon[$v['id']]) {
			return false;
		}
		if ($v['num'] + $user_coupon[$v['id']] > 3) {
			return false;
		}
		$coupon_to_write[$cache_prefix . 'COUPON:' . $v['id']] = $remaining_coupon[$v['id']] - $v['num'];
		$user_coupon_to_write[$v['id']] = $user_coupon[$v['id']] + $v['num'];
	}
	$redis->multi();
	$redis->sets($coupon_to_write);
	$redis->setx('hmset', $cache_prefix . 'USER_COUPON:' . $uid, $user_coupon_to_write);
	$res = $redis->exec();
	return $res;
}

function build_sign($data) {
	ksort($data);
	$query = array();
	foreach ($data as $k => $v) {
		$query[] = "$k=$v";
	}
	$query_string = implode('&', $query);
	$sign = strtolower(md5($query_string));
	return $sign;
}
