<?php

/*
** 提交数据到电信接口
*/
include_once (dirname(realpath(__FILE__)).'/telecomplan_a_init.php');
include_once (dirname(realpath(__FILE__)).'/Telecom3DES.php');

$exit_result = array(
	'status' => 1,
	'info' => ''
);

// 判断用户有没有次数
if (!$imei_status) {
	$exit_result['status'] = -1;
	$exit_result['info'] = '亲，您长时间未操作页面已过期，请退出活动页面重新打开~';
	exit(json_encode($exit_result));
}

// 判断用户初始化状态
if (!$init_status) {
	$exit_result['status'] = -3;
	$exit_result['info'] = '系统繁忙，请稍后再试~';
	exit(json_encode($exit_result));
}

$times = decrease_times_by1();
if ($times === false) {
	$exit_result['status'] = -2;
	$exit_result['info'] = '亲，您没有抽奖次数哦~';
	exit(json_encode($exit_result));
}

// 发送数据到电信接口
$telecom_ret = post_data_to_telecom($_POST);
$output_json = $telecom_ret['output_json'];
$errno = $telecom_ret['errno'];

$output = json_decode($output_json, true);
$return_code = $output['result'];
$return_msg = $output['msg'];
$return_telephone = $output['detail']['mobile'];

if ($return_code == 10000) {
	$status = 0;
	$info = '参与成功！';
} else {
	$status = 1;
	$info = $return_msg;
}

// 记日志
$log_data = array(
	'imsi' => $imsi,
	'imei' => $imei,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid ,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'telephone' => $_POST['telephone'],
	'return_code' => $return_code,
	'return_msg' => $return_msg,
	'output_json' => $output_json,
	'errno' => $errno,
	'key' => 'receive'
);
permanentlog('activity_'.$aid .'.log', json_encode($log_data));
$exit_result = array(
	'status' => $status,
	'info' => $info,
	'return_telephone' => $return_telephone ? $return_telephone : ''
);
exit(json_encode($exit_result));

function post_data_to_telecom($data) {
	$url = 'http://app.189.cn/task/finishTaskService.do';
	$partner_id = '100001798';
	$ad_id = '1440577634409007083';
	$app_id = 'anzhi';
	$key = '21CN886823CD9EF2C80134A15C8164ADF4CDC94APPONLINE';
	$finish_time = ceil(microtime(true)*1000);
	$mobile = $data['telephone'];
	
	$code = array(
		'ad_id' => $ad_id,
		'app_id' => $app_id,
		'finish_time' => $finish_time,
		'mobile' => $mobile,
	);
	
	// code需要用3DES加密
	$code = json_encode($code);
	$crypt = new Telecom3DES($key);
	$code = $crypt->encrypt($code);
	
	$post_data = array(
		'partner_id' => $partner_id,
		'code' => $code,
	);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
	$output_json = curl_exec($ch);
	$errno = curl_errno($ch);
	curl_close($ch);
	return array('output_json' => $output_json, 'errno' => $errno);
}