<?php
/*
** 下载抢红包
*/
include_once (dirname(realpath(__FILE__)).'/../init.php');
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$tplObj -> out['is_test'] = 1;
}	
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin();
$time = time();
//日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"time" => $time,
	'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
$tplObj -> out['DEVICEID'] = $_SESSION['DEVICEID'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj->display('lottery/down_grab_red.html');
exit;