<?php

/*
** 各种点击操作
*/

include_once (dirname(realpath(__FILE__)).'/init.php');

$exit_data = 0;

if (!$imsi_status) {
	exit(json_encode($exit_data));
}

$log_key = $_POST['log_key'];

$log_data = array();

if ($log_key == 'download_soft') {//下载软件点击
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
	// 下载软件
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
		'key' => $log_key
	);
	$exit_data = $now_num;
} else if ($log_key == 'share') {//分享点击
	$share_day = $redis->get($rkey_imsi_share);
	if ($share_day != $today) {
		// 可抽奖次数+1
		$now_num = get_lottery_num();
		$now_num += 1;
		set_lottery_num($now_num);
		// 分享日期更新
		$share_day = $today;
		$redis->set($rkey_imsi_share, $share_day, $r_cache_time);
		$exit_data = 1;
	}
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => $log_key
	);
	$exit_data = 0;
} else if ($log_key == 'install_soft') {//安装软件点击
	$package = $_POST['package'];
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
		'key' => $log_key
	);
	$exit_data = 0;
} else if ($log_key == 'go_weibo') {//去微博点击
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => $log_key
	);
	$exit_data = 0;
}

permanentlog($activity_log_file, json_encode($log_data));
exit(json_encode($exit_data));