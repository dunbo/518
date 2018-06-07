<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();
$active_id = 'aion';
$time = time();
if($_POST){
	//下载日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"package" => $_POST['pkg'],
		"gcid" => $_POST['gcid'],
		"time" => $time,
		'key' => 'download_soft'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	exit;
}else{
	//日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'key' => 'show_homepage'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	$tpl = "lottery/scuffle_journey/aion.html";
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> display($tpl);
}
