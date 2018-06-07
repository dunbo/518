<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin();
$time = time();
$sid = $sid ? $sid : session_id();
if(!isset($active_id)){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$active_id = 476;
	}else{
		$active_id = 490;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/scuffle_journey/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
//user_loging_reserve();

$tpl = "lottery/scuffle_journey/index.html";
if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
{
	$share_url = 'http://118.26.203.23/a_'.$active_id.'.html';
}
elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
{
	$share_url = 'http://9.m.anzhi.com/a_'.$active_id.'.html';
}
else 
{
	$share_url = 'http://fx.anzhi.com/a_'.$active_id.'.html';
}

$tplObj -> out['imsi'] = $_SESSION['USER_IMSI'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['share_text'] = "《乱斗西游2》老战友回归召集令，福利狂欢嘉年华，现在就重返战场！";
$tplObj -> out['share'] = $_GET['share'];
$tplObj -> out['share_url'] = $share_url;
$tplObj -> display($tpl);
