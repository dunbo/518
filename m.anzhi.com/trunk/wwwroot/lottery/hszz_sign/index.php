<?php
include_once ('./fun.php');

$build_query = http_build_query($_GET);

if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/hszz_sign/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();
$time = time();
//$time = get_now_time();
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
	$tm_config = get_config($prefix,$active_id,$uid);
	$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'royal_war_draw_award');
	// $kind_award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","royal_war_draw_gift");
	// if(!$kind_award_list && !$kind_award_gift){
	if(!$kind_award_list){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;
	}
	$tplObj -> out['lottery_num'] = get_lottery_num($prefix,$active_id,$uid);
	$tplObj -> out['is_day'] = $tm_config[date('Y-m-d',$time)]['num'];
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,"{$prefix}","valentine_draw_userinfo");
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];		
}else{//未登录
	$tm_config = get_config($prefix,$active_id,'');
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 1;	
}
if($uid){
	$sign_status=get_sign_status($prefix,$active_id,$_SESSION['USER_UID'],$time);
	if($sign_status == 1){
		$tplObj -> out['is_sing'] = 1;//当天是否签到过(1是2否)
	}else{
		$tplObj -> out['is_sing'] = 2;//当天是否签到过
	}
	//新增显示小星星
	$sign_days=get_sign_days($prefix,$active_id,$_SESSION['USER_UID']);
	$tplObj -> out['sign_days']=$sign_days;
	// echo "<pre>";var_dump($sign_days);die;
}
// $redis->set("hszzn:450_rest_lottery_num:".$_SESSION['USER_UID'],1000);
$tplObj -> out['tm_config'] = $tm_config;
$tplObj -> out['turn_aid'] = $tm_config[date('Y-m-d',$time)]['turn_aid'];
//实物中奖信息award_all
$tplObj -> out['award_all'] = get_award_all_top30($active_id,"{$prefix}",'royal_war_draw_award');
if($_GET['stop'] == 1){
	$tpl = "lottery/hszz_sign/end.html";
}else{
	$tpl = "lottery/hszz_sign/index.html";
}
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['pkg'] = 'com.hg.cloudsanddsgreddddasf';
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['uid'] = $uid;
$tplObj -> out['share'] = $_GET['share'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
