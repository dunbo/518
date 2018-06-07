<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);

if( $configs['is_test'] ) {
	$h_str = 'dev.';
}

$tplObj -> out['is_test'] = $configs['is_test'];

$center_url	=	"http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url	=	$center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
//日志
$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"	=>	$_SERVER['REMOTE_ADDR'],
		"sid"	=>	$sid,
		"time"	=>	$time,
		"user"	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key'	=>	'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
user_loging_new();

//获取配置活动信息
$prize			=	get_prize();
//获取配置的红包数
$red_conf_num	=	get_red_package_conf_num();

if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid'	=>	$_SESSION['USER_UID'],
		'imsi'	=>	$_SESSION['USER_IMSI'],
		'device_id'		=>	$_SESSION['DEVICEID'],
		'activity_id'	=>	$active_id,
		'ip'	=>	$_SERVER['REMOTE_ADDR'],
		'sid'	=>	$sid,
		'time'	=>	$time,
		'key'	=>	'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username']	=	$_SESSION['USER_NAME'];
	$tplObj -> out['is_login']	=	1;
	$tplObj -> out['uid']		=	$uid;
	//获取抽奖数
	$user_num = get_user_lottery_num($uid);
	//获取某局的中奖位置信息
	$level_list	=	lottery_level_list($active_id, $uid, $user_num['g_num']);
	//当前局抽中的奖品次数
	$red_package_num = count($level_list);
	//当前局中的红包
	$draw_num	=	get_cur_draw_num($level_list);
	$level_list	=	values2keys($level_list);
	//携带任务的软件包
	$package_list	=	soft_task_list($active_id, $uid);
	$package_task	=	!empty($package_list)?json_encode($package_list):'';
	
	//已完成的任务软件
	$package_done_list	=	soft_task_done_list($active_id, $uid);
	$package_task_done	=	!empty($package_done_list)?json_encode($package_done_list):'';
	//获取我的未拆红包
	$award_list	=	get_award($active_id, $uid);
	//print_r($user_num);die;
	$init_games	=	$activity["red_init_num"]?$activity["red_init_num"]:0;
	$games		=	$activity["red_init_num"]-$user_num["g_num"];
	$games		=	$games?$games:0;
	$g_num		=	$user_num["g_num"]?$user_num["g_num"]:0;
	$lottery_num	=	$user_num["lottery_num"]+$user_num["def_num"]-$user_num["end_num"];
	$lottery_num	=	$lottery_num?$lottery_num:0;
	
	$tplObj -> out['red_package_num']	=	$red_package_num;
	$tplObj -> out['draw_num']			=	$draw_num;
	$tplObj -> out['init_games']		=	$init_games;
	$tplObj -> out['games']				=	$games;
	$tplObj -> out['g_num']				=	$g_num;
	$tplObj -> out['lottery_num']		=	$lottery_num;
	$tplObj -> out['lottery_add']		=	$user_num['lottery_num']?$user_num['lottery_num']:0;
	
	$tplObj -> out['package_task']		=	$package_task;
	$tplObj -> out['package_task_done']	=	$package_task_done;
	$tplObj -> out['level_list']		=	$level_list;
	$tplObj -> out['user_num']			=	$user_num;
	$tplObj -> out['award_list']		=	$award_list;
}else {
	//未登录
	$tplObj -> out['activity']		=	$activity;
	$tplObj -> out['is_login']		=	2;
	$tplObj -> out['lottery_add']	=	0;
}

if($_GET['stop'] == 1) {
	//未开始
	$tpl = "lottery/{$prefix}/begin.html";
}else if($_GET['stop'] == 2){
	//已经结束
	$tpl = "lottery/{$prefix}/end.html";
}else {
	$tpl = "lottery/{$prefix}/index.html";
}

$tplObj -> out['is_share']		=	$_GET['is_share'];
$tplObj -> out['share']			=	$_GET['share'];
$tplObj -> out['activity']		=	$activity;
$tplObj -> out['prize']			=	$prize;
$tplObj -> out['red_conf_num']	=	$red_conf_num;

$img_host = getImageHost();
$tplObj -> out['img_url']		=	$img_host;

$tplObj -> out['time_key']		=	strtotime(date("Y-m-d", $time));
$tplObj -> out['aid']			=	$active_id;
$tplObj -> out['sid']			=	$_GET['sid'];
$tplObj -> out['stop']			=	$_GET['stop'];
$tplObj -> out['prefix']		=	$prefix;
$tplObj -> out['static_url']	=	$configs['static_url'];
$tplObj -> out['new_static_url']=	$configs['new_static_url'];
$tplObj -> out['version_code']	=	$_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);