<?php

include_once (dirname(realpath(__FILE__)).'/spring_init.php');

$name = trim($_GET['name']);
$telephone = trim($_GET['telephone']);
$address = trim($_GET['address']);

if ($name == '') {
    echo 501;//名字为空
    exit;
}

if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11){
	echo 500;//电话号码不对
	exit;
}

// 查找此用户中的哪个奖，然后决定需要填写的信息
$option = array(
    'where' => array(
        'imsi' => $imsi,
        'status' => 0,
    ),
    'table' => 'spring_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');
if (empty($result)) {
    echo 400;// 没有未填的中奖信息
    exit;
}

$award = $result['award'];

// 根据几等奖判断是否需要填写地址
$award_info = $award_config[$award-1];
$award_type = $award_info[2];
if ($award_type == 1) {
    // 是实物，判断是否没有填写地址
    if ($address == '') {
        echo 502;//地址为空
        exit;
    }
}

if (empty($address))$address='';

$where = array(
	'imsi' => $imsi,
	'award' => $award,
	'status' => 0
);

$data = array(
	'name' => $name,
	'telephone' => $telephone,
	'address' => $address,
	'time' => time(),
	'status' => 1,
	'__user_table' => 'spring_lottery_award'
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
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'award' => $award,
    'telphone' => $telephone,
    'name' => $name,
    'address' => $address,
    'time' => time(),
    'key' => 'spring_lottery_get_info'
);
permanentlog($activity_log_file, json_encode($log_data));

echo 200;
exit;