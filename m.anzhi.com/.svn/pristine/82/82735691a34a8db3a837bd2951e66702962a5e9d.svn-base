<?php
include_once ('./valentine_fun.php');
//$active_id =312;
$sid = $_GET['sid'];
$build_query = http_build_query($_GET);
$center_url = "http://i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/valentine.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
$active_id = $_GET['aid'];
session_begin();

//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => time(),
		"award_level" => "",//pid
		"user" => $_SESSION['USER_NAME'],
		"name" => "",
		"telphone" => "",
		"address" => "",
		"package" => "",
		"gift" =>  "",
		"users" => "",
		'uid'=>$uid,
		"lottery_type" => "",//1实物2礼包
		"award_name" => "",
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
		'time' => time(),
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	if($_GET['stop'] != 1){
		list($user_info,$rest) =get_rest_valentine($uid);
		$tplObj -> out['valentine_rest_num'] = $rest;
		$tplObj -> out['money'] = $user_info['money'];
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}
$tplObj -> out['aid'] = $active_id;

//前10的排行榜用户
$top10_user = get_top10_user();
$tplObj -> out['top10_user'] = $top10_user;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
if($_GET['stop'] == 1){
	//奖品
	$top10_prize = array(
		'Iphone6','IPAD air','小米4','500元充值卡','500元充值卡','500元充值卡','300元充值卡','300元充值卡','300元充值卡','300元充值卡'
	);
	$tplObj -> out['top10_prize'] = $top10_prize;	
	//所有实物奖
	$award_list = get_user_award();
	$tplObj -> out['award_list'] = $award_list;	
	$tpl = "lottery/valentine_end.html";
}else{
	$top10_user_arr = array(
		'厮杀至榜首','高居排行榜第二','暂居第三','登至排行榜第四','杀进排行榜前五','冲入排行榜第六','拼杀至排行榜第七','奋战入排行榜第八','冲入排行榜第九','挤入排行榜第十'
	);
	$tplObj -> out['top10_user_arr'] = $top10_user_arr;
	$tpl = "lottery/valentine.html";
}
$tplObj -> display($tpl);