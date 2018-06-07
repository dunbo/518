<?php
/*
** 下载软件接口
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
	'status' => 0,
);

if (!$imsi_status) {
    $return_arr['status'] = 300; //没插sim卡
    echo json_encode($return_arr);
    exit;
}
//同一秒操作进黑名单
brush_second_do($aid, 1);
$now_num = get_lottery_num();

$package = $_POST['pkgname'];
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
	// 下的是有效的包
	// 根据包名获取真正的包，记载缓存也是记真正的包名
	$real_package = $redis->get("real_package:post_old_package_{$package}:");
	if (!$real_package) {
		$real_package = get_real_package($package);
	}
	// 检查此用户是否曾经下载过
	$ever_download = $redis->gethash($rkey_imsi_package_list, $real_package);
	if (!$ever_download) {
		// 在用户下载列表中新增此包
		$redis->sethash($rkey_imsi_package_list, array($real_package=>1), $r_cache_time);
		// 抽奖次数+1
		$now_num += 1;
		set_lottery_num($now_num);
	}
}

$return_arr = array(
	'status' => 200,
	'lottery_num' => $now_num,
);
// 下载成功记录日志
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
echo json_encode($return_arr);
exit;