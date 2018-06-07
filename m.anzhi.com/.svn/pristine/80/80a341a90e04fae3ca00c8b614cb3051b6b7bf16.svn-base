<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = $_POST['aid'];
session_begin();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$package = $_POST['package'];

$my_need = array($imsi,$package);

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_POST['sid'],
	'time' => time(),
	'package' => $package,
	'key' => 'download_soft'
);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
