<?php
/*
** 下载软件
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

$package = $_POST['package'];
$softid = $_POST['softid'];
$type = $_POST['type'];

if($type==1){
	$key = 'download_soft';
}else if($type==2){
	$key = 'install_soft';
}else if($type==3){
	$key = 'open_soft';
}

// 下载记录日志
$log_data = array(
	'imsi' => $imsi,
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'device_id' => $_SESSION['DEVICEID'],
	'sid' => $sid,
	'time' => $now,
	'softid' => $softid,
	'package' => $package,
	'key' => $key,
);
permanentlog($activity_log_file, json_encode($log_data));

if($type==1){
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
		$real_package = $redis->get("real_package:post_old_package_{$package}");
		if (!$real_package) {
			$real_package = get_real_package($package);
		}
		// 检查此用户是否曾经下载过
		$ever_download = $redis->gethash($rkey_imsi_package_list, $real_package);
		if (!$ever_download) {
			// 在用户下载列表中新增此包
			$redis->sethash($rkey_imsi_package_list, array($real_package=>1), $r_cache_time);
			// 下载次数+1
			if(!$redis->exists($rkey_imsi_download_num)){
		        $redis->set($rkey_imsi_download_num, 0, $r_cache_time);
		    }
		    $redis->setx('incr', $rkey_imsi_download_num, 1);
			echo 1;exit;
		}
	}
}

echo 2;