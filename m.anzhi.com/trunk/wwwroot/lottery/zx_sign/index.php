<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();
//日志
$log_data = array(
		"imsi"	=>	$_SESSION['USER_IMSI'],
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
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176) {//已登录
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
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	$tm_config = get_config($active_id,$uid);
	if($tm_config[date('Y-m-d',$time)]['status'] == 1) {
		$tplObj -> out['is_sign'] = 1;//当天是否签到过(1是2否)
	}else {
		$tplObj -> out['is_sign'] = 2;//当天是否签到过
	}
	//获取该用户实物
	//$kind_award_list = get_user_kind_award_new($uid, $active_id, $prefix, 'valentine_draw_award');
	// 	if(!$kind_award_list ) {
	// 		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	// 	}else {
	// 		$tplObj -> out['is_user_winning'] = 1;
	// 	}
	$lottery_num = get_lottery_num($uid);
	$tplObj -> out['lottery_num'] = $lottery_num;
}else {//未登录
	$tm_config = get_config($active_id,'');
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 5;	
}
$award_all = get_award_all_new($active_id, $prefix, 'valentine_draw_award');
foreach($award_all as $k => $v){
	$pos = strpos($v['prizename'],":");
	if($pos !== false || !$v){
		unset($award_all[$k]);
	}
}
$tplObj -> out['award_all'] = $award_all;//获取获奖人信息奖品

if($_GET['stop'] == 1) {
	$tpl = "lottery/{$prefix}/end.html";
}else {
	$tpl = "lottery/{$prefix}/index.html";
}

$tplObj -> out['time_key'] = strtotime(date("Y-m-d", $time));
$tplObj -> out['tm_config'] = $tm_config;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
