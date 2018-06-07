<?php
include_once ('./common.php');
#var_dump($_GET);
$tplObj->out['new_static_url'] = $configs['new_static_url'];
$platform = $_SESSION['product'];
#var_dump($_SESSION);
$build_query = http_build_query($_GET);
if ($configs['is_test']){
	$h_str = 'dev.';
	$tplObj->out['is_test'] = 1;
	$page_url = "http://m.test.anzhi.com/lottery/preorder/index.php?" . $build_query;
} else {
	$page_url = "http://m.anzhi.com/lottery/preorder/index.php?" . $build_query;
}

$center_url = "http://" . $h_str . "i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url . $page_url;
$tplObj->out['login_url'] = $login_url;
$tplObj->out['version_code'] = $_SESSION['VERSION_CODE'];
$aid = $_GET['aid'];
$tplObj->out['aid'] = $aid;

$uid = $_SESSION['USER_ID'];
if (!empty($uid) && $uid != '13176') {
	$username = $_SESSION['USER_NAME'];
	$tplObj->out['is_login'] = true;
} else {
	$username = '';
	$tplObj->out['is_login'] = false;
}
$tplObj->out['username'] = $username;
$time = time();

if ($time < strtotime('2018-02-29') && $_GET['period'] != 'end') {
	$tpl = "lottery/preorder/index.html";

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
		$coupon[] = $v;
		$ids[] = $v['id'];
	}

	# 获取所有礼券当前剩余数量
	$coupon_amount = get_coupon_remaining_amount($ids);
	foreach ($coupon as $k => $v) {
		$coupon[$k]['remaining_amount'] = $coupon_amount[$v['id']];
	}

	# 获取用户已购买礼券数量
	$user_coupon_amount = get_user_coupon_amount($uid);
	foreach ($coupon as $k => $v) {
		$coupon[$k]['available_amount'] = empty($user_coupon_amount[$v['id']]) ? 3 : 3 - $user_coupon_amount[$v['id']];
	}

	$unpaid_num = get_unpaid_order_num($uid);
	$tplObj->out['unpaid_num'] = $unpaid_num;
	$tplObj->out['coupon'] = $coupon;
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
		"platform" => $platform,
		"key" => 'show_homepage',
	);
	permanentlog('activity_'.$aid.'.log', json_encode($log_data));
} else {
	$tpl = "lottery/preorder/end.html";
}

$tplObj->display($tpl);
