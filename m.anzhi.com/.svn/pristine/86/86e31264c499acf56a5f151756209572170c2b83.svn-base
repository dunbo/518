<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
	'status' => 0,
	'puzzle_num' => 0,
	'lottery_num' => 0,
);

if (!$imsi_status) {
	exit(json_encode($return_arr));
}

// 用户当前的可抽奖次数
$now_num = get_lottery_num();
$puzzle_num = get_puzzle_num();

if($puzzle_num<3)//说明拼图已经3次 不给机会了
{
	if($_GET['is_success']==1)
	{
		//拼图成功了
		$now_num += 1;
		set_lottery_num($now_num);
		$puzzle_num = $redis->setx('incr',$rkey_imsi_puzzle,1);	
		// 记日志
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'key' => 'pizzle_success'
		);
		permanentlog($activity_log_file, json_encode($log_data));
	}else if($_GET['is_success']==2)//抓取失败
	{
		//拼图失败了
		$puzzle_num = $redis->setx('incr',$rkey_imsi_puzzle,1);	
		// 记日志
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'key' => 'puzzle_lose'
		);
		permanentlog($activity_log_file, json_encode($log_data));
	}
}

$return_arr = array(
	'status' => 200,
	'puzzle_num' => $puzzle_num,
	'lottery_num' => $now_num,
);
exit(json_encode($return_arr));