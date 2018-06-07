<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
if($_POST['sid'] && eregi('[0-9a-zA-z]', $_POST['sid']) && strlen($_POST['sid']) == 32){
	session_id($_POST['sid']);
}
session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$telphone = $_POST['telphone'];
$active_id = 213;
if(!preg_match("/^1[34578][0-9]{9}$/",$telphone) || strlen($telphone) != 11){
	echo 500;
	return 500;
	exit;
}
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_POST['sid'],
	'telphone' => $telphone,
	'time' => time(),
	'key' => 'download_soft'
);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));