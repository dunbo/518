<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
session_begin();
$aid = $_GET['aid'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
//$_GET['hqg_from'] = 'stop';
$imsi = $_SESSION['USER_IMSI'];
if($_GET['hqg_from'] == 'stop'){
	$tplObj -> display("lottery/hqg_sign_end.html");
}else{
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',
		'key' => 'show_homepage'
	);
	permanentlog('activity_'.$aid .'.log', json_encode($log_data));		
	$tplObj -> display("lottery/hqg_sign.html");
}