<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
session_begin();
$aid = $_GET['aid'];
$telephone = $_GET['telephone'];
$download = $_GET['download'];
$imsi = $_SESSION['USER_IMSI'];
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'type' => $download,
	'key' => 'download_soft'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11){
	echo 500;
	exit;
}

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'telphone' => $telephone,
	'key' => 'telphone'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

echo 200;
return 200;