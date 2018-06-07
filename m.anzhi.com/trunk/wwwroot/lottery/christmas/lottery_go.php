<?php
include_once ('./fun.php');
session_begin($sid);
$time = time();
//抽奖跳转日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		"is_luxury" => $_GET['is_luxury'],//1豪华大转盘2缤纷大转盘3我要兑奖4我要抽奖
		'key' => 'lottery_go'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

