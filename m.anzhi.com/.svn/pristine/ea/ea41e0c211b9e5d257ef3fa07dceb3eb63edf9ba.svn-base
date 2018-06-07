<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
//日志
$log_data = array(
	"imsi"	=>	$_SESSION['USER_IMSI'],
	"device_id"		=>	$_SESSION['DEVICEID'],
	"mac" 	=>  $_SESSION['MAC'],
	"mid"	=>	$m_arr['id'],
	"ip"	=>	$_SERVER['REMOTE_ADDR'],
	"sid"	=>	$sid,
	"time"	=>	$time,
	"user"	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	'uid'	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
	'key'	=>	'show_homepage'
);
permanentlog($log_key, json_encode($log_data));	
user_loging_new();
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid'	=>	$_SESSION['USER_UID'],
		'imsi'	=>	$_SESSION['USER_IMSI'],
		'device_id'		=>	$_SESSION['DEVICEID'],
		"mac" 	=> $_SESSION['MAC'],
		"mid"	=>	$m_arr['id'],
		'ip'	=>	$_SERVER['REMOTE_ADDR'],
		'sid'	=>	$sid,
		'time'	=>	$time,
		'key'	=>	'login'
	);
	permanentlog($log_key, json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	//签到提醒开关状态
	$tplObj -> out['switch_remind'] = get_remind_status($uid);	
}else {//未登录
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 5;	
	$tplObj -> out['switch_remind'] = 0;	
}
//var_dump($_SESSION['USER_NAME'],$_SESSION);
$sign_config = sign_config($uid);
$tplObj -> out['sign_config'] = $sign_config;
//连续签到
$day =  $sign_config[$today]['level'];	
$tplObj -> out['day'] = $day;
$continuity = get_sign_days($uid,1,$sign_config,$day);
$tplObj -> out['continuity_sign_days'] = $continuity ? $continuity : 0;
//签到总天数
$sign_total = get_sign_days($uid,2,$sign_config,$day);
$tplObj -> out['sign_total_days'] = $sign_total ? $sign_total : 0;

$tpl = "{$prefix}/index.html";

$tplObj -> out['time_key'] = strtotime(date("Y-m-d", $time));
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['m_arr'] = $m_arr;
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['img_url']  = getImageHost();
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
//当天是否签到
$tplObj -> out['is_sign'] = $sign_config[$today]['status'];
//是否抽奖或拆红包或送金币
$is_lottery_key = "{$prefix}:{$m_arr['id']}:is_lottery:".$uid.":".$today;
$is_lottery = $redis->get($is_lottery_key);
$tplObj -> out['is_lottery'] = $is_lottery ? $is_lottery : 0;
//当天是抽奖或拆红包
$tplObj -> out['red_or_lottery'] = $sign_config[$today]['type'];
//did	
$tplObj -> out['did'] = $sign_config[$today]['did'];
//红包id
$tplObj -> out['redid'] = $sign_config[$today]['redid'];
//连续签到配置
$continuity_config = get_continuity_config($uid);
$tplObj -> out['attendance'] = get_attendance($continuity_config);
$tplObj -> out['continuity_config'] = $continuity_config;
//连续签到获得奖品最近30条数据
$tplObj -> out['ontinuity_top30'] =	get_continuity_top30();
//补签卡数量
$card_config = signature_card_config();
$tplObj -> out['card_config'] = $card_config;
$tplObj -> out['cards_num'] = get_cards_num($uid);
//后台签到提醒配置开关
$tplObj -> out['admin_switch_remind'] = get_admin_switch_remind();
//var_dump(get_red_now(2055));
$tplObj -> display($tpl);
