<?php
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

//防刷，下载完成的时间纪录下来
$down_key_today = 'gen_brush:down:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
$redis->setnx($down_key_today,$now);
$redis->expire($down_key_today,86400*60);

// 用户当前的可抽奖次数
$now_num = get_lottery_num();

$package = $_POST['package'];
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
	// 根据出来的包名获取真正的包  记载缓存也是记真正的包名
	$real_package = $redis->get("real_package:post_old_package_{$package}:");
	if(!$real_package)
	{
		$real_package = get_real_package($package);
	}
	$ever_download = $redis->gethash($rkey_imsi_package_list, $real_package);
	if (!$ever_download) {
		// 在用户下载列表中新增此包
		$redis->sethash($rkey_imsi_package_list, array($real_package=>1), $r_cache_time);
		// 可抽奖次数+1
		$now_num += 1;
		set_lottery_num($now_num);
	}
}
$puzzle_num = $redis->get($rkey_imsi_puzzle);

$return_arr = array(
	'status' => 200,
	//'ever_shared' => $ever_shared,
	'puzzle_num' => $puzzle_num,
	'lottery_num' => $now_num,
);
//下载成功
$log_data = array(
	'imsi' => $imsi,
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'device_id' => $_SESSION['DEVICEID'],
	'sid' => $sid,
	'time' => time(),
	'package' => $package,
	'real_package' => $real_package,
	'key' => 'download_soft_success'
);
permanentlog($activity_log_file, json_encode($log_data));
exit(json_encode($return_arr));