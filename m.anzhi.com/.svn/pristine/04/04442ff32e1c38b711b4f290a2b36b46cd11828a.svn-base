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
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$configs['activity_url']."/lottery/ranking/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
$tplObj->out ['prefix_url'] = $configs['activity_url'];

list($ranking_config,$activity_arr) = get_config($active_id,$_GET['ap_id']);
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
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
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
		list($money,$lottery_num) = user_lottery_num($uid,$active_id);	
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;	
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}

if($_GET['stop'] == 1){
	//所有实物奖
	list($award_list,$award_count) = get_award_all($active_id);
	$tplObj -> out['award_list'] = $award_list;
	$tplObj -> out['award_count'] = $award_count;
	//用户礼包兑换信息
	$gift_prize_list = get_user_kind_gift($uid,$active_id);
	//用户实物兑换信息
	$kind_award_list = get_user_kind_award($uid,$active_id);
	if(!$gift_prize_list && !$kind_award_list){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;//有中奖信息	
	}	
	//奖品
	$ap_award = explode('；',$ranking_config['ap_award']);	
	$tplObj -> out['top10_prize'] = $ap_award;	
	$tplObj -> out['top10_prize_str'] = json_encode($ap_award);		
	$tpl = "lottery/ranking/end.html";
}else{
	$tplObj -> out['money'] = $money ? $money : 0;
	$tplObj -> out['lottery_num'] = $lottery_num;
	//$tplObj -> out['lottery_num'] = 100; //todo
	$tpl = "lottery/ranking/index.html";
}
//跑马灯轮播最近的10条排行榜信息
$top10_ranking = get_top10_ranking($ranking_config['yes_marquee'],$active_id);
$tplObj -> out['top10_ranking'] = $top10_ranking;	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$ranking_config['rank_lottery_desc_text'] = str_replace(array("\r\n", "\r", "\n"), "", htmlspecialchars_decode($ranking_config['rank_lottery_desc_text']));
$tplObj -> out['ranking_config'] = $ranking_config;
$tplObj -> out['end_page'] = intval($ranking_config['is_score'] % 10); 
$tplObj -> out['page_count'] = ceil($ranking_config['is_score']/10); 
$tplObj -> out['prize_pic'] = explode(',',$ranking_config['prize_pic']);
$tplObj -> out['img_url'] = getImageHost();
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['tpl'] = 'index';	


if($ranking_config['share_add_all']==1&&$_GET['stop']!=1){
    //新的充值抽奖才有
    $tplObj -> out['top10_prize'] = get_top10_prize($active_id);
    list($list,$prize_level) = get_prize_name($active_id);
    $tplObj -> out['prize_results'] = $prize_level;
    //$tplObj -> out['award_list'] = get_award_all($active_id);
    //用户礼包兑换信息
    $gift_prize_list = get_user_kind_gift($uid,$active_id);
    //用户实物兑换信息
    $kind_award_list = get_user_kind_award($uid,$active_id);
    if(!$gift_prize_list && !$kind_award_list){
            $tplObj -> out['is_user_winning'] = 2;//无中奖信息	
    }else{
            $tplObj -> out['is_user_winning'] = 1;//有中奖信息	
    }
    $tplObj -> out['is_share'] = $_GET['is_share'];
    $tplObj -> out['tpl'] = 'lottery';	
    $tpl = 'lottery/ranking/index_lottery.html';
}
$tplObj -> display($tpl);
