<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
session_begin();
$aid = $_GET['aid'];
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
	'key' => 'share_soft'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));
echo 200;
return 200;

