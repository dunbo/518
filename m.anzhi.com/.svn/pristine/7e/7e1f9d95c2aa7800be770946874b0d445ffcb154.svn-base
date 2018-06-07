<?php
include_once ('./common.php');

$pay_url = "http://h5pay.anzhi.com/h5pay/market/order/enter/consume";

$uid = $_SESSION['USER_ID'];
if (empty($uid) || $uid == '13176') {
	exit('{"status":2}');
}
$username = $_SESSION['USER_NAME'];
$aid = $_GET['aid'];
$time = time();
$custom = array(
	'aid' => $aid,
	'imsi' => $_SESSION['USER_IMSI'],
	'device_id' => $_SESSION['DEVICEID'],
);

if (!empty($_POST['goods'])) {
	$order_coupon = $_POST['goods'];

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
	$ids = array();
	foreach ($res as $v) {
		$coupon[$v['id']] = $v;
		$ids[] = $v['id'];
	}

	# 获取所有礼券当前剩余数量
	$coupon_amount = get_coupon_remaining_amount($ids);
	foreach ($coupon as $k => $v) {
		$coupon[$k]['remaining_amount'] = $coupon_amount[$k];
	}

	# 获取用户已购买礼券数量
	$user_coupon_amount = get_user_coupon_amount($uid);
	foreach ($coupon as $k => $v) {
		$coupon[$k]['available_amount'] = empty($user_coupon_amount[$k]) ? 3 : 3 - $user_coupon_amount[$k];
	}

	$flag = true;
	foreach ($order_coupon as $v) {
		if ($v['num'] > min($coupon[$v['id']]['remaining_amount'], $coupon[$v['id']]['available_amount'])) {
			$flag = false;
			break;
		}
	}

	if ($flag) {
		$order_id = generate_order_id();
		$res = adjust_coupon_amount($uid, $order_coupon);
		$res = true;
		if ($res) {
			$coupon_set = array();
			$price = 0;
			foreach ($order_coupon as $v) {
				$coupon_set[$v['id']] = (int)$v['num'];
				$price += $coupon[$v['id']]['coupon_real_price'] * $v['num'];
			}
			$data = array(
				'__user_table' => 'preorder_user_order',
				'order_id' => $order_id,
				'user_id' => $uid,
				'ucenter_uid' => $_SESSION['USER_UID'],
				'coupon_set' => json_encode($coupon_set),
				'price' => $price,
				'status' => 0,
				'order_time' => $time,
				'expire_time' => $time + 360,
			);
			$res = $model->insert($data, 'lottery/lottery');
			if ($res) {
				// 构造支付链接
				$query = array(
					'appkey' => $app_key,
					'time' => $time . '000',
					'out_time' => 360,
					'open_id' => $_SESSION['USER_UID'],
					'sid' => $_SESSION['UCENTER_SID'],
					'amount' => $price,
					'cporder' => $order_id,
					'name' => '预购折扣礼券',
					'custom' => json_encode($custom),
					'return_url' => 'http://promotion.anzhi.com/lottery/preorder/jump.php?aid=' . $aid,
				);
				$check_data = $query;
				$check_data['app_secret'] = $app_secret;
				$sign = build_sign($check_data);
				$query['sign'] = $sign;
				$query_string = http_build_query($query);
				$jump_url = $pay_url . '?' . $query_string;
				$log_data = array(
					"imsi" => $_SESSION['USER_IMSI'],
					"device_id" => $_SESSION['DEVICEID'],
					"activity_id" => $aid,
					"ip" => $_SERVER['REMOTE_ADDR'],
					"sid" => $sid,
					"time" => $time,
					"user" => $username,
					"uid"=> $uid,
					"ucenter_uid" => $_SESSION['USER_UID'],
					"coupon" => $coupon_set,
					"price" => $price,
					"pay_url" => $jump_url,
					"key" => "order",
				);
				permanentlog('activity_'.$aid.'.log', json_encode($log_data));
				exit('{"status":1,"jump_url":"' . $jump_url . '"}');
			}
		}
	}
} elseif (!empty($_POST['order_id'])) {
	$order_id = $_POST['order_id'];
	$option = array(
		'table' => 'preorder_user_order',
		'where' => array(
			'order_id' => $order_id,
			'status' => 0,
		),
	);
	$order = $model->findOne($option, 'lottery/lottery');
	if (!empty($order) && $order['user_id'] == $uid) {
		$query = array(
			'appkey' => $app_key,
			'time' => $order['order_time'] . '000',
			'out_time' => $order['expire_time'] - $order['order_time'],
			'open_id' => $_SESSION['USER_UID'],
			'sid' => $_SESSION['UCENTER_SID'],
			'amount' => $order['price'],
			'cporder' => $order_id,
			'name' => '预购折扣礼券',
			'custom' => json_encode($custom),
			'return_url' => 'http://promotion.anzhi.com/lottery/preorder/jump.php?aid=' . $aid,
		);
		$check_data = $query;
		$check_data['app_secret'] = $app_secret;
		$sign = build_sign($check_data);
		$query['sign'] = $sign;
		$query_string = http_build_query($query);
		$jump_url = $pay_url . '?' . $query_string;
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $aid,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"user" => $username,
			"uid"=> $uid,
			"ucenter_uid" => $_SESSION['USER_UID'],
			"coupon" => json_decode($order['coupon_set'], true),
			"price" => $order['price'],
			"pay_url" => $jump_url,
			"key" => "re_pay",
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		exit('{"status":1,"jump_url":"' . $jump_url . '"}');
	}
}
exit('{"status":0}');

