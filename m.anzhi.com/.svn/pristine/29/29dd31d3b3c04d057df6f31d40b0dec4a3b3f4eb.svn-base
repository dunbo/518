<?php
/*
** 兑吧推广升级push到达页面用户
*/
include_once (dirname(realpath(__FILE__)).'/../init.php');
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$tplObj -> out['is_test'] = 1;
}	
$active_id = "against_push";
$time = time();
if($_GET['is_log'] == 1){
	//下载日志
	$log_data = array(
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"time" => $time,
		'key' => 'download'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	exit;
}
//日志
$log_data = array(
	// "imsi" => $_SESSION['USER_IMSI'],
	// "device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"time" => $time,
	// "user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	// 'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
	'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
$tplObj->display('lottery/against_push.html');
exit;