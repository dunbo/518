<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$aid = $_GET['aid'];

if($_GET['lottery'] == 1){
$log_data = array(
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'extend_lottery'
);
}else{
$log_data = array(
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'extend_share'
);
}
permanentlog('activity_'.$aid.'.log', json_encode($log_data));
echo 200;
return 200;