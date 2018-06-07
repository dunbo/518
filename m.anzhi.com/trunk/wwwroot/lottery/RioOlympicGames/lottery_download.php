<?php
include_once ('./init.php');
$return_arr = array(
	'status' => 0,
	//'ever_shared' => 0,
	'puzzle_num' => 0,
	'lottery_num' => 0,
);

/*if (!$imsi_status) {
	exit(json_encode($return_arr));
}*/

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
	$ever_download = $redis->gethash($rkey_imsi_package_list, $package);
	if (!$ever_download) {
		// 在用户下载列表中新增此包
		$redis->sethash($rkey_imsi_package_list, array($package=>1), $r_cache_time);
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
exit(json_encode($return_arr));