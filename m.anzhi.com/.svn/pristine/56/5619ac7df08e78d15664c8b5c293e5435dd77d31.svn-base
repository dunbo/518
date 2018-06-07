<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);

if( $configs['is_test'] ) {
	$h_str = 'dev.';
}

$tplObj -> out['is_test'] = $configs['is_test'];

$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
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
user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	
	$tm_config = get_config($active_id, $uid);
	$tplObj -> out['tm_config'] = $tm_config;

	//获取我的礼包或礼券记录
	$kind_award_list = get_user_kind_award_list($uid,$active_id,'qmqj_sign','valentine_draw_award');

	$tplObj -> out['kind_award_list'] = $kind_award_list;
}else {//未登录
	$tplObj -> out['is_login'] = 2;
	$tm_config = get_config($active_id,'');
}

$tpl = "lottery/qmqj_sign/index.html";

$tplObj -> out['time_key'] = strtotime(date("Y-m-d", $time));
$tplObj -> out['days'] = $tm_config;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['stop'] = $_GET['stop'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];

$tplObj -> display($tpl);