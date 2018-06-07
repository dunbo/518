<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();
$active_id = 'new';
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin();
$time = time();
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'tpl' => $_GET['tpl'],
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
//user_loging_reserve();
if($_GET['tpl'] == 2){
	$tpl = "lottery/scuffle_journey/new_index2.html";
}else{
	$tpl = "lottery/scuffle_journey/new_index.html";
}
$tplObj -> out['imsi'] = $_SESSION['USER_IMSI'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['share'] = $_GET['share'];
$tplObj -> out['share_url'] = $share_url;
$tplObj -> display($tpl);
