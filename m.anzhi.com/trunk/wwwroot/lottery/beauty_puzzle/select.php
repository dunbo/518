<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$opt	=	(int)$_POST['opt'];
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
			'key'			=>	'select_button',
			'type'			=>	$opt,
	);
	permanentlog($activity_log_file, json_encode($log_data));
	echo 1;die;
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
			'key'			=>	'select_button',
			'type'			=>	$opt,
	);
	permanentlog($activity_log_file, json_encode($log_data));
	echo 2;die;
}
