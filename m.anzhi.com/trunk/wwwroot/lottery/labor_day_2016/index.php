<?php
include_once ('./fun.php');
session_begin();
if($_POST['p']){
	$prize_info = get_page_prize($_POST['p']);
	$data = array(
		'now_time'=>$time,
		'data' => $prize_info
	);
	exit(json_encode($data));
}
if($_POST['chances'] == 1){
	$ret = get_chances($_SESSION['USER_UID']);
	exit(json_encode($ret));
}
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;	
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;

//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
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
	if($_GET['stop'] != 1){
		$tplObj -> out['rest_integral'] = get_rest_integral($uid);
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}

if($_GET['stop'] == 1){
	//所有实物奖
	$award_list = get_user_award();
	$tplObj -> out['award_list'] = $award_list;	
	$tpl = "lottery/{$prefix}/end.html";
}else{
	//奖品
	$prize_info = array();
	for ($i = 1; $i <= 4; $i++) {
		$prize = get_prize($i);
		$prize_info[$i] = $prize;
	}	
	$tplObj -> out['prize_list'] = $prize_info;	
	//跑马灯轮播最近的10条兑奖信息
	$top10_prize = get_top10_prize();
	$tplObj -> out['top10_prize'] = $top10_prize;	
	$tpl = "lottery/{$prefix}/index.html";
}
$kind_award_list = get_user_kind_award($uid);
if(!$kind_award_list){
	$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
}else{
	$tplObj -> out['is_user_winning'] = 1;//有中奖信息	
}
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);