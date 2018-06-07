<?php
/*
** 活动中奖信息编辑接口
*/

include_once (dirname(realpath(__FILE__)).'/double11_init.php');

if (!$imsi_status) {
	echo -1;exit;
}

$award_id = $_POST['award_id'];
if (empty($award_id)) {
	echo -2;exit;
}


if ($now > $activity_end_time) {
	echo -5;exit;
}

$name = trim($_POST['name']);
$telephone = trim($_POST['telephone']);

if (empty($name)) {
	echo 500;
	exit;
}

if (empty($telephone)) {
	echo 501;
	exit;
}

// 检查名字长度，不要超过10个字
if (mb_strlen($name, 'utf-8') > 10) {
	echo 502;//名字太长
	exit;
}

if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11){
	echo 503;//电话号码不对
	exit;
}

// 检查一下此award_id是否为此用户中的奖
$option = array(
	'where' => array(
		'id' => $award_id,
		'imsi' => $imsi,
	),
	'table' => 'double11_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');
if (!$find) {
	echo -3;exit;
}

// 判断一下中奖的类型
$award_level = $find['award'];
$award_type = $award_config[$award_level-1][2];
if ($award_type != 0 && $award_type != 1) {
	exit -4;exit;
}

$where = array(
	'imsi' => $imsi,
	'id' => $award_id
);

$data = array(
	'name' => $name,
	'telephone' => $telephone,
	'time' => time(),
	'status' => 1,
	'__user_table' => 'double11_lottery_award'
);

$result = $model -> update($where,$data,'lottery/lottery');
if (empty($result)) {
    echo 300;
    exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'award_level' => $award_level,
    'telphone' => $telephone,
    'name' => $name,
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'info_edit'
);
permanentlog($activity_log_file, json_encode($log_data));

echo 200;
exit;