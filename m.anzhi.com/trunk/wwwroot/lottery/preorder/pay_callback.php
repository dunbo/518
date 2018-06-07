<?php
include_once ('./common.php');
file_put_contents('/tmp/pay.log', json_encode($_POST) . "\n", FILE_APPEND);

if (!empty($_POST)) {
	if ($_POST['appkey'] != $app_key)
		exit('error');
	$query = $_POST;
	unset($query['sign']);
	$query['app_secret'] = $app_secret;
	$sign = build_sign($query);
	if ($_POST['sign'] != $sign)
		exit('error');
	$custom = json_decode($_POST['custom'], true);
	$aid = $custom['aid'];
	$order_id = $_POST['cporder'];
	$time = time();
	$pay_time = floor($_POST['notify_time'] / 1000);
	$pay_order_id = $_POST['order_id'];
	$option = array(
		'table' => 'preorder_user_order',
		'where' => array(
			'order_id' => $order_id,
		),
	);
	$order = $model->findOne($option, 'lottery/lottery');
	$sql = "update preorder_user_order set pay_time=$pay_time, pay_order_id='$pay_order_id',status=(case when status=0 then 1 else -2 end) where order_id='$order_id'";
	$res = $model->query($sql, 'lottery/lottery');
	if ($res) {
		$log_data = array(
			"imsi" => $custom['imsi'],
			"device_id" => $custom['device_id'],
			"activity_id" => $aid,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"time" => $time,
			"uid"=> $order['user_id'],
			"ucenter_uid" => $order['ucenter_uid'],
			"coupon" => json_decode($order['coupon_set'], true),
			"price" => $order['price'],
			"order_id" => $order_id,
			"key" => "pay_success",
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		exit('success');
	}
	else
		exit('error');
}

