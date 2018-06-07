<?php
include_once ('./fun.php');
session_begin($sid);
if($_POST['p']){
	$limit = $_POST['limit'] ? $_POST['limit'] :10;
	$list = get_page_ranking($_POST['p'],$active_id,$limit);
	exit(json_encode($list));
}
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/".$prefix."/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;

$config = get_config($active_id,$_GET['ap_id']);
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
user_loging_new();
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
	brush_second_do($active_id);
	get_brush_bysn();
	//登录日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	if($_GET['stop'] != 1){
		if($config['free_day_switch'] == 1){
			//登录每天获得一次抽奖机会
			$login_num_key = "{$prefix}:".$active_id.":login_lottery_num:".$uid.":".$today;
			add_lottery_num($uid,$config['lottery_num_limit'],$login_num_key,86400,$config['acrivity_day']);
		}
		$lottery_num = user_lottery_num($uid);	
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;	
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}
if($_GET['stop'] == 1){
	$tpl = "lottery/".$prefix."/end.html";
}else{
	$tplObj -> out['lottery_num'] = isset($lottery_num) ? $lottery_num : 1;
	$tpl = "lottery/".$prefix."/index.html";
	list($prize_name,$prize_level) = get_prize_name($active_id);
	$tplObj -> out['prize_results'] = $prize_level;
	$tplObj -> out['prize_limit'] = count($prize_name);
}
	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$config['prize_pic_arr'] = json_decode($config['prize_pic'],true);
$tplObj -> out['list'] = $config;
$img_host = getImageHost();
if($_SESSION['VERSION_CODE'] >= 6400){
	$prize_pic_arr =  array();
	foreach($config['prize_pic_arr'] as $v){
		$prize_pic_arr[] = $img_host.$v;
	}
	$tplObj -> out['json_pic'] = json_encode($prize_pic_arr);
}
$tplObj -> out['img_url'] = $img_host;
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['activity_host'] = $activity_host;
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);