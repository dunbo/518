<?php

/*
** 通过活动获取抽奖
*/

include_once (dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
	'status' => 0,
	'grasp_num' => 0,
	'lottery_num' => 0,
);

if (!$imsi_status) {
	exit(json_encode($return_arr));
}

// 用户当前的可抽奖次数
$now_num = get_lottery_num();


if($_GET['is_success']==1)
{
	//抓取成功了
	$now_num += 1;
	set_lottery_num($now_num);
	$grasp_num = $redis->setx('incr',$rkey_imsi_grasp,1);	
	// 记日志
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'key' => 'grasp_success'
	);
	permanentlog($activity_log_file, json_encode($log_data));
}
else if($_GET['is_success']==2)//抓取失败
{
	//抓取失败了
	$grasp_num = $redis->setx('incr',$rkey_imsi_grasp,1);	
	// 记日志
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'key' => 'grasp_lose'
	);
	permanentlog($activity_log_file, json_encode($log_data));
}

$return_arr = array(
	'status' => 200,
	'grasp_num' => $grasp_num,
	'lottery_num' => $now_num,
);

exit(json_encode($return_arr));