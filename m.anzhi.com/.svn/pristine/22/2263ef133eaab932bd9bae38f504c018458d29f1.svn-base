<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$sid = $_GET['sid'];
session_begin($sid);
$time = time();
if($_GET['game_name']!='')
{
	$game_prefix=$_GET['game_name'];
	$game_name=$_GET['game_name'];
}
else
{
	$game_name="royal_war";
}
$log_data = array(
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'key' => 'button_click',
		'game_name'=>$game_name,
);
permanentlog('reserve.log', json_encode($log_data));

if ($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176) { //是否为登录
	//已经登录
	setcookie('IS_JUMP', '1', time()+24 * 3600 * 15, '/', 'anzhi.com');
	//预约成功日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'success',
		'game_name'=>$game_name,
		);
		permanentlog('reserve.log', json_encode($log_data));
	echo 1;
} else {
	//无登录
	echo 2;
}
