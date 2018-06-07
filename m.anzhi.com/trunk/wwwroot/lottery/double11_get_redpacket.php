<?php

/*
** 端内的抽红包接口
*/

require_once(dirname(realpath(__FILE__)) . '/double11_init.php');

$ret_json = array('status' => 0, 'data' => '', 'msg' => '');

if (!$imsi_status) {
	$ret_json['status'] = -1;
	exit(json_encode($ret_json));
}

// 判断用户今天是第几次抽红包
$get_redpacket_times = $redis->setx('HINCRBY', $rkey_imsi_open_info, $today, 1);
if ($get_redpacket_times > 5) {
	$redis->sethash($rkey_imsi_open_info, $today, 5);
	// 用户已抽过5次，不可以再抽
	$ret_json['status'] = 0;
	$ret_json['data'] = 0;
	$ret_json['msg'] = '';
	exit(json_encode($ret_json));
}

$min = 0; $max = 5;
if ($get_redpacket_times == 1) {
	// 今天第一次拆，最少能拆出一个
	$min = 1;
}
$redpacket = mt_rand($min, $max);

// 判断用户获得的总数
$open_get_info = $redis->setx('HINCRBY', $rkey_imsi_open_get_total_info, $today, $redpacket);
if ($open_get_info > 5) {
	// 每天拆出的红包数超过5，不可以
	$decrease = 0 - $redpacket;
	$redis->setx('HINCRBY', $rkey_imsi_open_get_total_info, $today, $decrease);
	$redpacket = 0;
}

$redis->set($rkey_imsi_open_get_info, $redpacket, $r_cache_time);

if ($redpacket > 0) {
	// 更新用户的可抽奖次数
	$lottery_num = get_lottery_num();
	$lottery_num += $redpacket;
	set_lottery_num($lottery_num);
}
$ret_json['status'] = 0;
$ret_json['data'] = $redpacket;
exit(json_encode($ret_json));