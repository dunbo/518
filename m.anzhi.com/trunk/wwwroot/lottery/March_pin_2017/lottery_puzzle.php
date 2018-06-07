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
	//记录某一时刻拼图的次数
	$balck_cache_time = '5184000';//redis缓存时间为两个月
	$black_list = "black_name_list:{$aid}";
	$black_list_name = $redis->gethash($black_list,$imsi);
	if(!$black_list_name)//如果不在黑名单
	{
		$tm = time();
		$rkey_imsi_puzzle_num = "puzzle:{$aid}:{$imsi}:{$tm}:puzzle_num";
		$puzzle_num = $redis->setx('incr', $rkey_imsi_puzzle_num, 1);
		$redis->expire($puzzle_num,20);
		if($download_succ_num>=2)
		{
			$redis->sethash($black_list, array($imsi=>1), $balck_cache_time);
		}
	}

	if($_GET['is_puzzle']==1) //如果点击拼图了 就减少一次机会
	{
		$puzzle_num = $redis->setx('incr',$rkey_imsi_puzzle,1);
	}
	if($_GET['is_success']==1)
	{
		//拼图成功了
		$now_num += 1;
		set_lottery_num($now_num);	
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