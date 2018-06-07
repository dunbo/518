<?php
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
echo 1;
