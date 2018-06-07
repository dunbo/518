<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$opt		=	(int)$_POST['opt'];
$step		=	(int)$_POST['step'];
$pkgname	=	$_POST['pkgname'];
$softid		=	$_POST['softid'];

$type	=	$opt.'-'.$step;
// 记日志
$log_data = array(
		'imsi'			=>	$_SESSION['USER_IMSI'],
		'device_id'		=>	$_SESSION['DEVICEID'],
		'activity_id'	=>	$aid,
		'sid'			=>	$sid,
		'ip'			=>	$_SERVER['REMOTE_ADDR'],
		'package'		=>	$pkgname,
		'softid'		=>	$softid,
		'time'			=>	time(),
		'users'			=>	'',
		'uid'			=>	'',
		'key'			=>	'download_soft',
		'type'			=>	$type,
);
permanentlog($activity_log_file, json_encode($log_data));

$condition = array(
	'1-1'	=>	1,
	'1-3'	=>	3,
	'2-1'	=>	1,
	'2-3'	=>	3,
);

$num 		=	set_download_num($imsi, $opt, $step);
$cond_num	=	isset($condition[$type])?$condition[$type]:0;
//是否解锁
if( $num >= $cond_num ) {
	$re = set_unlocck($imsi, $opt, $step);
	if($re) {
		del_download_num($imsi, $opt, $step);
		echo 1;
	}else {
		echo 0;
	}
}else {
	echo 0;
}
