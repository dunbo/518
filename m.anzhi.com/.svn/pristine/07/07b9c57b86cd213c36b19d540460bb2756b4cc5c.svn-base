<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}

$active_id = 200;
session_start();

if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$share_id = $_GET['share_id'];
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'share_id' => $share_id,
	'key' => 'download_soft'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['share_id'] = $share_id;
$tplObj -> display("lights_share.html");
