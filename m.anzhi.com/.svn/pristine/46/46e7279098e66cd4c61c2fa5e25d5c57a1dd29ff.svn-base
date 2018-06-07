<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
session_begin();
$time = time();
$active_id = $_GET['aid'];
$sid = $_GET['sid'];
$uid = $_SESSION['USER_UID'];
$package = $_GET['package'];
//用户点击
$log_data = array(
	'imsi' => $_SESSION['USER_IMSI'],
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $sid,
	'time' => time(),
	'package' => $package,
	'type' => $_GET['type'],
	'tpl' => $_GET['tpl'],
	'key' => 'download_soft'
	
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	

echo 1;