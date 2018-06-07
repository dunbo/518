<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1){
	$h_str = 'dev.';
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();
if($_GET['is_log'] == 1){
	//页面日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'package' => $_GET['package'],
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		//1点击icon 2点击开始游戏 3点击礼包tab 4点击攻略 5点击论坛
		"type" => $_GET['type'],
		'key' => 'click'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
}
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
		'is_register' => $_GET['is_register'],//1注册0登录
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}
// $gift_list =  array();
// $prize_count = get_prize_count();
// for($i=1;$i<=$prize_count;$i++){
	// $gift_list[$i] = get_gift_list($i);
	// $prize_num_key = "{$prefix}:{$active_id}_prize_num:".$i;
	// $gift_list[$i]['num'] = $redis->get($prize_num_key);
	// $gift_list[$i]['is_lottery'] = get_is_lottery($i,$gift_list[$i]['id'],$uid);
// }
//var_dump(get_gift_list());exit;
$tplObj -> out['gift_list'] = get_gift_list();	
$is_lottery_arr = get_is_lottery($uid);
$tplObj -> out['is_lottery_arr'] =  $is_lottery_arr;
//跑马灯、结束页面中奖信息
//$tplObj -> out['award_all'] = get_award_all_new($active_id,"{$prefix}",'valentine_draw_gift');
$tpl = "lottery/{$prefix}/index.html";
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['is_test'] = $configs['is_test'];
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
