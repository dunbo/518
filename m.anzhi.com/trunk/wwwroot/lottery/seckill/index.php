<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);

if( $configs['is_test'] ) {
	$h_str = 'dev.';
}

$tplObj -> out['is_test'] = $configs['is_test'];

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
	'product' => $_SESSION['product'],//1市场 13 sdk
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
	
	$home_key_today = $prefix.":".$active_id.':home:'.$uid;
	$redis->set($home_key_today,$time,10);
	
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	$az_money = get_azmoney($uid);
	$tplObj -> out['az_money'] = $az_money['azmoney'] ? $az_money['azmoney'] : 0;
	$tplObj -> out['isHasPayPwd'] = $az_money['isHasPayPwd'];
}else {//未登录
	$tplObj -> out['is_login'] = 2;
}
$award_all = get_award_all_new($active_id, $prefix, 'valentine_draw_award');
foreach($award_all as $k => $v){
	$pos = strpos($v['prizename'],":");
	if($pos !== false || !$v){
		unset($award_all[$k]);
	}
    $pos2 = strpos($v['prizename'],"礼券");
    if($pos2 !== false) unset($award_all[$k]);	
}
$tplObj -> out['award_all'] = $award_all;//获取获奖人信息奖品

if($_GET['stop'] == 1){
	$tpl = "lottery/{$prefix}/end.html";
}else{
	$h_arr = get_now_h($time);
	$H = $h_arr['now_h'];
	if($_GET['next'] == 'next_tab'){
		if($H == "00"){
			$time = $time+8*3600;
		}else{
			$time = $time+4*3600;
		}
	}else if($_GET['next'] == 'next_tab2'){
		if($H == "00"){
			$time = $time+12*3600;
		}else{
			$time = $time+8*3600;
		}	
	}
	$config = get_prize_config(date("Ymd",$time),$time);
	if($_GET['next']){
		exit(json_encode($config));
	}
	$tpl = "lottery/{$prefix}/index.html";
	$tplObj -> out['time_key'] = strtotime(date("Y-m-d", $time));
	$tplObj -> out['config'] = $config;
	if($config[0]['level_ids'] == '57,58,59,60'){
		unset($h_arr['next_tab']);
		unset($h_arr['next_tab2']);//最后一天
	}else if($config[0]['level_ids'] == '53,54,55,56'){
		unset($h_arr['next_tab2']);
	}
	$tplObj -> out['h_arr'] = $h_arr;
}
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['stop'] = $_GET['stop'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];

$tplObj -> display($tpl);