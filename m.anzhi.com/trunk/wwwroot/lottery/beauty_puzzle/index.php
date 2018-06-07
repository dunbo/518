<?php

require_once(dirname(realpath(__FILE__)) . '/init_page.php');
// 记日志
$log_data = array(
		'imsi'			=>	$_SESSION['USER_IMSI'],
		'device_id'		=>	$_SESSION['DEVICEID'],
		'activity_id'	=>	$aid,
		'sid'			=>	$sid,
		'ip'			=>	$_SERVER['REMOTE_ADDR'],
		'time'			=>	time(),
		'users'			=>	'',
		'uid'			=>	'',
		'key'			=>	'index'
);
permanentlog($activity_log_file, json_encode($log_data));
$tplObj->display('lottery/beauty_puzzle/index.html');
exit;