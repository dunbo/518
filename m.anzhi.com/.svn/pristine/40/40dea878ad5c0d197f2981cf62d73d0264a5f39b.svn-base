<?php

// 下载软件，增加可抽奖次数的接口

require_once(dirname(realpath(__FILE__)) . '/octoberflight_init.php');

if (!$imsi_status) {
	echo 0;
	exit;
}

// 用户当前的可抽奖次数
//$now_num = $redis->get($rkey_imsi_lottery_num);
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
		//$redis->set($rkey_imsi_lottery_num, $now_num, $r_cache_time);
		set_lottery_num($now_num);
	}
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'package' => $package,
	'users' => '',
    'uid' => '',
    'key' => 'download_soft'
);
permanentlog($activity_log_file, json_encode($log_data));

echo $now_num;
exit;