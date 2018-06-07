<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;	
}
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
	//每天登录送锤子
	login_add_lottery($uid);
	
	if($_GET['stop'] != 1){
		$lottery_num = get_rest_lottery($uid);
		$tplObj -> out['lottery_num'] = $lottery_num;
	}
	$deduction_lottery_num = get_deduction_lottery_num($uid);
	$tplObj -> out['deduction_lottery_num'] = $deduction_lottery_num ? $deduction_lottery_num :0;		

	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	//兑换记录
	$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');
	$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","valentine_draw_gift");
	if(!$kind_award_list && !$kind_award_gift){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;
	}
	
	//检查在新版砸蛋结束页中是否有领取记录
	if( isset($_GET['new']) && $_GET['stop'] == 1 ){
		$pid_arr = get_lottrey_pid();
		foreach($kind_award_list as $k => $v){
			if($pid_arr[$v['pid']] <=8){
				unset($kind_award_list[$k]);
			}
		}
		if($kind_award_list){
			$tplObj -> out['is_user_receive'] = 1;		//有领取记录
		}else{
			$tplObj -> out['is_user_receive'] = 2;		//无领取记录
		}
	}
	
	//幸运值
	$tplObj -> out['luk_num'] = get_luk($uid);
}else{//未登录
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 100;
}

if($_GET['stop'] == 1){
	if((strtotime("2016-05-31 23:59:00")+(15*86400)) <= $time){
		$tplObj -> out['is_stop15'] = 1;	//活动结束完15天
	}	
	if( isset($_GET['new']) ){
		$tpl = "lottery/{$prefix}/end_new.html";
	}else{
		$tpl = "lottery/{$prefix}/end.html";
	}
}else{
	if( isset($_GET['new']) ){
		$tpl = "lottery/{$prefix}/index_new.html";
	}else{
		$tpl = "lottery/{$prefix}/index.html";
	}
}
$ap_award = array("2000元京东卡","小米note","1000元充值卡","800元充值卡","拍立得","500元充值卡","周大福神兽金饰","300京东卡","98纯银项链","黄金兽摆件");
$tplObj -> out['top10_prize'] = $ap_award;	
//跑马灯轮播最近的10条排行榜信息
$top10_ranking = get_top10_ranking($active_id);
$tplObj -> out['top10_ranking'] = $top10_ranking;	

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);