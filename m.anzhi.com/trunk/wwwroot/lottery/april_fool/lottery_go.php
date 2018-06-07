<?php
include_once ('./fun.php');
session_begin($sid);
if($_GET['no_login'] == 1){
	//未登录点击抽奖
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		"is_luxury" => $_GET['is_luxury'],//1转盘抽奖2下载抽奖
		'key' => 'no_login_lottery'
	);	
}else{
	//抽奖跳转日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		"is_luxury" => $_GET['is_luxury'],//1转盘抽奖2下载抽奖
		'key' => 'lottery_go'
	);
}
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
