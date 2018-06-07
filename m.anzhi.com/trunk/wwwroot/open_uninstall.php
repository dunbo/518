<?php

//打开下载页面，记录日志
include_once (dirname(realpath(__FILE__)).'/init.php');
if($_POST['SID'] && $_POST['VC'] && $_POST['SDK_INT'] && $_POST['DEVICE_ID'] && $_POST['MODEL'] && $_POST['FROM'] == 'uninstall'){
	$pid = isset($_POST['pid']) ? $_POST['pid'] : 1;
	$log_arr = array(
		'KEY' => 'open_unintall',
		'SID' => $_POST['SID'],
		'VC' => $_POST['VC'],
		'SDK_INT' => $_POST['SDK_INT'],
		'DEVICE_ID' => $_POST['DEVICE_ID'],
		'MODEL' => $_POST['MODEL'],
		'TIME' => time(),
		'pid' => $pid,
	);
	if(permanentlog("open_unintall.log",json_encode($log_arr))){
		echo 1;
		return 1;
		exit;
	}else{
		echo 2;
		return 2;
		exit;
	}
}
