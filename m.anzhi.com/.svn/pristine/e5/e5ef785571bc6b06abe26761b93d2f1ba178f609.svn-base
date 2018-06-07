<?php
/*
** 提交手机号处理接口
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

$data = array(
	'status' => 0,
	'msg' => ''
);
if (!$imsi_status) {
	$data['status'] = 3;
    $data['msg'] = '没插sim卡';
    exit(json_encode($data));
}
$telephone = trim($_POST['telephone']);
if (!$telephone) {
	$data['status'] = 1;
	$data['msg'] = '手机号不能为空！';
	exit(json_encode($data));
}

$reg = '/^1[34578][0-9]{9}$/';
if (!preg_match($reg, $telephone)) {
	$data['status'] = 2;
	$data['msg'] = '请输入正确的手机号';
	exit(json_encode($data));
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $sid,
    'telphone' => $telephone,
    'time' => time(),
    'key' => 'get_telephone'
);
permanentlog($activity_log_file, json_encode($log_data));

$data['status'] = 0;
$data['msg'] = '提交成功！';
exit(json_encode($data));