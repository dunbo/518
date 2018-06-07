<?php

/*
** 分享动作，如果今天未分享过，可抽奖次数+1，否则可抽奖次数不变
** 返回分享动作后用户的可抽奖次数
*/

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');

if (!$imsi_status) {
	echo 0;
	exit;
}

$today = date("Y-m-d");

// 获得此用户的可抽奖次数
$my_num = $redis->get($rkey_imsi_lottery_num);
if ($my_num === null) {
	$option = array(
		'where' => array(
			'imsi' => $imsi,
		),
		'field' => 'lottery_num',
		'table' => 'aprilstrip_lottery_num',
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if (!empty($find['lottery_num'])) {
		$my_num = $find['lottery_num'];
	} else {
		$my_num = 0;
	}
	// 写redis
	$redis->setx('incr', $rkey_imsi_lottery_num, 0);
}

// 判断用户今天是否分享过
$last_share_day = $redis->get($rkey_imsi_last_share_day);
$add_flag = false;
if ($last_share_day === null) {
	// 之前没有分享过，此用户的可抽奖次数+1
	$my_num++;
	$add_flag = true;
} else {
	// 分享过，判断分享时间是否为今天
	if ($last_share_day != $today) {
		// 今天未分享过
		$my_num++;
		$add_flag = true;
	}
}

if ($add_flag) {
	// 更新缓存
	$redis->set($rkey_imsi_last_share_day, $today, $r_cache_time);
	$redis->set($rkey_imsi_lottery_num, $my_num, $r_cache_time);
	// 入库
	$option = array(
		'where' => array(
			'imsi' => $imsi,
		),
		'field' => 'lottery_num',
		'table' => 'aprilstrip_lottery_num',
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if (!empty($find)) {
		// 更新
		$update_data = array(
			'lottery_num' => $my_num,
			'time' => time(),
			'__user_table' => 'aprilstrip_lottery_num'
		);
		$where = array(
			'imsi' => $imsi
		);
		$model->update($where, $update_data, 'lottery/lottery');
	} else {
		// 新增
		$insert_data = array(
			'imsi' => $imsi,
			'lottery_num' => $my_num,
			'time' => time(),
			'__user_table' => 'aprilstrip_lottery_num'
		);
		$model->insert($insert_data, 'lottery/lottery');
	}
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'aprilstrip_share_num'
);
permanentlog($activity_log_file, json_encode($log_data));

echo $my_num;
exit;

