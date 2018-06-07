<?php

include_once (dirname(realpath(__FILE__)).'/weixin_init.php');

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'actsid' => $actsid,
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'jump'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display('lottery/guessappbattle/jump.html');