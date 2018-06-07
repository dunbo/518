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
		list($res_integral,$lottery_num) = get_rest_integral($uid);
		$tplObj -> out['rest_integral'] = $res_integral;
		$tplObj -> out['lottery_num'] = $lottery_num;
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	//兑换记录
	$kind_award_list = get_user_kind_award($uid);
	$my_prize_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","valentine_draw_gift");	
	if(!$kind_award_list && !$my_prize_gift){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;//有中奖信息	
	}	
	//用户信息
	get_user_info_new($uid,$active_id,$prefix,"one_dollar_userinfo");
	//我的奖品
	$my_prize_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');

	if(!$my_prize_list && !$my_prize_gift){
		$tplObj -> out['is_my_prize'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_my_prize'] = 1;//有中奖信息	
	}
}else{//未登录
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 3;
}

if($_GET['stop'] == 1){
	$award_all = get_award_all_new($active_id,$prefix,'one_dollar_kind_award');
	foreach($award_all as $k => $v){
		if($v['prizename'] == '游戏豪华礼包') unset($award_all[$k]);
	}
	$tplObj -> out['award_all'] = $award_all;

        if((strtotime("2016-07-11 23:59:00")+(15*86400)) <= $time){
                $tplObj -> out['is_stop15'] = 1;	//活动结束完15天
        }			
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
	foreach($top10_prize as $k => $v){
		if($v['prizename'] == '游戏豪华礼包') unset($top10_prize[$k]);
	}	
	$tplObj -> out['top10_prize'] = $top10_prize;	

	$tpl = "lottery/{$prefix}/index.html";
}
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
