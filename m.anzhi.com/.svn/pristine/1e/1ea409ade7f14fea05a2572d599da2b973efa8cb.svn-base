<?php
/*
** 用户中奖信息编辑接口
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

if (!$imsi_status) {
	echo -1;exit; //用户不合法
}

$award_id = $_POST['award_id'];
if (empty($award_id)) {
	echo -2;exit; //没中奖
}
if ($now > $a_end_time) {
	echo -5;exit; //活动已过期
}

$name = trim($_POST['name']);
$telephone = trim($_POST['telephone']);
$address = trim($_POST['address']);
if (empty($name)) {
	echo 500; //名字不能为空
	exit;
}
if (empty($telephone)) {
	echo 501; //手机号不能为空
	exit;
}
if (mb_strlen($name, 'utf-8') > 10) {
	echo 502; //名字太长
	exit;
}
if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11) {
	echo 503; //手机号码格式不对
	exit;
}
if(empty($address)){
	echo 504; //地址不能为空
	exit;
}

// 检查此award_id是否为此用户中的奖
$option = array(
	'where' => array(
		'id' => $award_id,
		'imsi' => $imsi,
		'aid' => $aid,
	),
	'table' => 'imsi_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');
if (!$find) {
	echo -3;exit;
}

// 修改用户中奖信息
$where = array(
	'imsi' => $imsi,
	'id' => $award_id,
	'aid' => $aid,
);
$data = array(
	'name' => $name,
	'telephone' => $telephone,
	'address' => $address,
	'time' => time(),
	'status' => 1,
	'__user_table' => 'imsi_lottery_award'
);
$result = $model->update($where, $data, 'lottery/lottery');
if (empty($result)) {
    echo 300;
    exit;
}

// 记录日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'award_level' => $award_level,
    'tel' => $telephone,
    'name' => $name,
	'address' => $address,
    'time' => time(),
	'award_id' => $award_id, //中奖纪录在表里的id
    'key' => 'info_edit'
);
permanentlog($activity_log_file, json_encode($log_data));

echo 200;
exit;