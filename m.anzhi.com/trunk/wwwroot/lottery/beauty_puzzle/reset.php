<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$opt	=	(int)$_GET['opt'];
if( $opt == 1 ) {
	$log_data = array(
			'imsi'			=>	$_SESSION['USER_IMSI'],
			'device_id'		=>	$_SESSION['DEVICEID'],
			'activity_id'	=>	$aid,
			'sid'			=>	$sid,
			'ip'			=>	$_SERVER['REMOTE_ADDR'],
			'time'			=>	time(),
			'users'			=>	'',
			'uid'			=>	'',
			'key'			=>	'reset_button',
			'type'			=>	$opt,
	);
	permanentlog($activity_log_file, json_encode($log_data));
}elseif( $opt == 2 ){
	$log_data = array(
			'imsi'			=>	$_SESSION['USER_IMSI'],
			'device_id'		=>	$_SESSION['DEVICEID'],
			'activity_id'	=>	$aid,
			'sid'			=>	$sid,
			'ip'			=>	$_SERVER['REMOTE_ADDR'],
			'time'			=>	time(),
			'users'			=>	'',
			'uid'			=>	'',
			'key'			=>	'reset_button',
			'type'			=>	$opt,
	);
	permanentlog($activity_log_file, json_encode($log_data));
}

$url = $activity_host."/lottery/beauty_puzzle/index.php?aid=".$aid;
header("Location: {$url}");