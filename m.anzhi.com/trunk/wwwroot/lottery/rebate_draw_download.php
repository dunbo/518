<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');

session_begin();
$activity_id = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];
$package = $_GET['package'];

$log_data = array(
	'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $activity_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'num' => 1,
	'package' => $package,
	'key' => 'download_soft'
);

permanentlog('activity_'.$activity_id.'.log', json_encode($log_data));

echo 200;
return 200;


