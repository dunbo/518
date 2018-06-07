<?php

/*
** 软件下载接口
*/

include_once (dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
	'status' => 0,
	//'ever_shared' => 0,
	'puzzle_num' => 0,
	'lottery_num' => 0,
);

if (!$imsi_status) {
	exit(json_encode($return_arr));
}

// 用户当前的可抽奖次数
$now_num = get_lottery_num();

/*$package = $_POST['package'];
$option = array(
	'where' => array(
		'package' => $package,
		'page_id' => $page_id,
		'status' => 1
	),
	'cache_time' => '86400',
	'table' => 'sj_actives_soft',
);
$find = $model->findOne($option);

if ($find) {
	// 下的是有效的包，检查此用户是否曾经下载过
	$ever_download = $redis->gethash($rkey_imsi_package_list, $package);
	if (!$ever_download) {
		// 在用户下载列表中新增此包
		$redis->sethash($rkey_imsi_package_list, array($package=>1), $r_cache_time);
		// 可抽奖次数+1
		$now_num += 1;
		set_lottery_num($now_num);
	}
}*/
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

/*$ever_shared =0;
$share_day = $redis->get($rkey_imsi_share);
if (!empty($share_day)) {
	$ever_shared = 1;
}*/
/*$ever_puzzled =0;
$puzzle_time = $redis->get($rkey_imsi_puzzle);
if (!empty($puzzle_time)) {
	$ever_puzzled = $puzzle_time;
}*/

$return_arr = array(
	'status' => 200,
	//'ever_shared' => $ever_shared,
	'puzzle_num' => $puzzle_num,
	'lottery_num' => $now_num,
);

exit(json_encode($return_arr));