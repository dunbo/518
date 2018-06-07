<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

if ($imsi_status != 1) {
	echo -1;
	exit;
}

$name = trim($_POST['name']);
$telephone = trim($_POST['telephone']);
$address = trim($_POST['address']);
$award_id = trim($_POST['award_id']);

if (empty($name)) {
	echo 500;
	exit;
}

if (empty($telephone)) {
	echo 501;
	exit;
}
if (empty($address)) {
	echo 504;
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


// 查找此用户中的哪个奖，然后决定需要填写的信息
$option = array(
    'where' => array(
        'imsi' => $imsi,
        'status' => 0,
		'aid' => $aid,
		'id' => $award_id,
    ),
    'table' => 'imsi_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');

if (empty($result)) {
    echo 400;// 没有未填的中奖信息
    exit;
}

$award = $result['award'];

$where = array(
	'imsi' => $imsi,
	'award' => $award,
	'status' => 0,
	'aid' => $aid,
	'id' => $award_id,
);

$data = array(
	'name' => $name,
	'telephone' => $telephone,
	'address' => $address,
	'time' => time(),
	'status' => 1,
	'__user_table' => 'imsi_lottery_award'
);

$result1 = $model -> update($where,$data,'lottery/lottery');

if (empty($result1)) {
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
    'award_level' => $award,
    'telphone' => $telephone,
	'address' => $address,
    'name' => $name,
    'time' => time(),
	'award_id' => $award_id,//中奖纪录在表里的id
    'key' => 'award'
);
permanentlog($activity_log_file, json_encode($log_data));

echo 200;
exit;