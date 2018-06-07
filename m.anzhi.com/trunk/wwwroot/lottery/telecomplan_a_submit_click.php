<?php
/*
** 记录用户点击下载、安装、打开的行为接口
*/

include_once (dirname(realpath(__FILE__)).'/telecomplan_a_init.php');

if (!$imei_status) {
	exit(0);
}

if (!$init_status) {
	exit(0);
}

$times = get_num();
$package = $_POST['package'];

if ($_POST['action'] == 'open_soft') {
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
		$ret = $redis->gethash($rkey_imei_package_list, $package);
		if (!$ret) {
			$redis->sethash($rkey_imei_package_list, array($package => 1));
			// 可发送次数+1
			$times = increase_times_by1();
		}
	}
}

// 记录日志
$log_data = array(
	'imsi' => $imsi,
	'imei' => $imei,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid ,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'package' => $package,
	'key' => $_POST['action'],
);
permanentlog('activity_'.$aid .'.log', json_encode($log_data));

// 返回可抽奖次数
$times = $times ? $times : 0;
echo $times;
exit;