<?php
include_once ('./xy2_fun.php');
session_begin();
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/xy2/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
//$active_id =312;
$active_id = $_GET['aid'];
$sid = $_GET['sid'];

$time = time();
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
user_loging();
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
		list($money,$lottery_num) = user_lottery_num($uid);
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	$kind_award_list = get_user_kind_award($uid);
	$kind_award_gift = get_user_kind_gift($uid);
	if(!$kind_award_list && !$kind_award_gift){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;//有中奖信息	
	}	
}else{//未登录
	$tplObj -> out['is_login'] = 2;
	$lottery_num  = 3;
}

if($_GET['stop'] == 1){
	//所有实物奖
	$tplObj -> out['award_list'] = get_award_all();
	$tpl = "lottery/xy2/end.html";
}else{
	//跑马灯轮播最近的10条兑奖信息
	$top10_prize = get_top10_prize();
	$tplObj -> out['top10_prize'] = $top10_prize;	
	$tplObj -> out['money'] = $money ? $money : 0;
	$tpl = "lottery/xy2/index.html";
}
$tplObj -> out['lottery_num'] = $lottery_num;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);