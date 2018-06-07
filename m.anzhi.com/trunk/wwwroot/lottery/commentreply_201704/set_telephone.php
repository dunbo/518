<?php

/*
** 提交的手机号码
*/

require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$data = array(
	'status' => 0,
	'msg' => ''
);
$telephone = trim($_POST['telephone']);
if (!$telephone) {
	$data['status'] = 1;
	$data['msg'] = '请输入手机号';
	exit(json_encode($data));
}

$reg = '/^1[34578][0-9]{9}$/';
if (!preg_match($reg, $telephone)) {
	$data['status'] = 1;
	$data['msg'] = '请输入正确的手机号';
	exit(json_encode($data));
}

// 记日志
$log_data = array(
    'imsi' => $_SESSION['USER_IMSI'],
    'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'telphone' => $telephone,
    'time' => time(),
    'key' => 'set_telephone'
);
permanentlog($activity_log_file, json_encode($log_data));

$data['status'] = 0;
$data['msg'] = '提交成功！';
exit(json_encode($data));