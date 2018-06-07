<?php
include_once ('./common.php');
$tplObj->out['new_static_url'] = $configs['new_static_url'];
$cache_prefix = 'PREORDER:COUPON:';
$tpl = "lottery/preorder/pay_record.html";	
$uid = $_SESSION['USER_ID'];
$aid = $_GET['aid'];
if (empty($uid) || $uid == '13176') {
	$tplObj->out['is_login'] = false;
	$username = '';
} else {
	$tplObj->out['is_login'] = true;
	$username = $_SESSION['USER_NAME'];
}
$time = time();
if ($_GET['from'] == 'end') {
	$tplObj->out['from'] = '&period=end';
} else {
	$tplObj->out['from'] = '';
}

$record = get_success_order($uid);
$tplObj->out['record'] = $record;
$tplObj->out['aid'] = $aid;

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
	"key" => "pay_record",
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

$tplObj->display($tpl);
