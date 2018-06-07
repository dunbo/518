<?php
include_once ('./ask_fun.php');
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/{$prefix}_index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();

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

	$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');//获取该用户实物
	$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","valentine_draw_gift");//获取该用户礼包
	if(!$kind_award_list && !$kind_award_gift){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;
	}
	$tplObj -> out['lottery_num'] = get_lottery_num($uid);
}else{//未登录
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 1;	
}

//跑马灯、结束页面中奖信息
$tplObj -> out['award_all'] = get_award_all_new($active_id,"{$prefix}",'valentine_draw_award');
if($_GET['stop'] == 1){
	$tpl = "lottery/{$prefix}/{$prefix}_end.html";
}else{
	$tpl = "lottery/{$prefix}/{$prefix}_index.html";
}
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
