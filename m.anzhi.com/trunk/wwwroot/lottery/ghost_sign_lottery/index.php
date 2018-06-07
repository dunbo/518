<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/ghost_sign_lottery/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();
//$time = time();
$time = get_now_time();
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
	$tm_config = get_config($active_id,$uid);
	$tplObj -> out['tm_config'] = $tm_config;
	if($tm_config[date('Y-m-d',$time)]['status'] == 1){
		$tplObj -> out['is_sing'] = 1;//当天是否签到过(1是2否)
	}else{
		$tplObj -> out['is_sing'] = 2;//当天是否签到过
	}
	$kind_award_list = get_user_kind_award_new($uid,$active_id,'ghost_sign_lottery','valentine_draw_award');//获取该用户实物
	//$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"ghost_sign_lottery","valentine_draw_gift");//获取该用户礼包
	if(!$kind_award_list){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;
	}
	$tplObj -> out['lottery_num'] = get_lottery_num($active_id,$uid);
}else{//未登录
	$tplObj -> out['num'] = array(1,2,3,4,5,6,7);
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 1;	
}

//$tplObj -> out['award_all'] = get_award_all_new($active_id,"ghost_sign_lottery",'valentine_draw_award');
$award_all = get_award_all_new($active_id,"ghost_sign_lottery",'valentine_draw_award');
foreach($award_all as $k => $v)//去掉中5元礼券的 名单
{
	if($v['prizename']=='5元礼券')
	{
		unset($award_all[$k]);
	}
}
$tplObj -> out['award_all'] = $award_all;
//获取获奖人信息奖品
if($_GET['stop'] == 1){
	$tpl = "lottery/ghost_sign_lottery/end.html";
}else{
	$tpl = "lottery/ghost_sign_lottery/index.html";
}
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

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['share_text'] = "《倩女幽魂》签到送豪礼，100%中奖";
$tplObj -> out['share_url'] = $share_url;
$tplObj -> out['is_stop'] = $_GET['stop'];
$tplObj -> display($tpl);
