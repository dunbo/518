<?php
include_once ('./2017_12_fun.php');
$build_query = http_build_query($_GET);
if(is_weixin() || $_GET['is_weixin']){
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tpl = "lottery/vip/weixin.html";
	$tplObj -> display($tpl);
	exit;
}
if( $configs['is_test'] ) {
	$h_str = 'dev.';
}
$last_operation = $prefix.":".$active_id.":last_operation:".$_SESSION['DEVICEID'].":".$today;
if($_GET['login_check']){
	$arr = array(
		'is_drop' => $_GET['is_drop'],
		'from_type' => $_GET['from_type']
	);
	$redis->set($last_operation,$arr,60);
}
$tplObj -> out['is_test'] = $configs['is_test'];

$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/{$tpl_prefix}_index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
$tplObj -> out['activity_host'] = $activity_host;
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
//$_SESSION['USER_ID'] = 13176;
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	unset($_COOKIE['_AZ_COOKIE_']);
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
	$last_arr = $redis->get($last_operation);
	$tplObj -> out['is_drop'] = $last_arr['is_drop'];
	$tplObj -> out['from_type'] = $last_arr['from_type'];
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
//是否有免费抽奖机会【针对设备】
$imei_key = $prefix.":".$active_id.":is_lottery:".$_SESSION['DEVICEID'].":".$today;
//是否有免费抽奖机会【针对用户】
$user_key = $prefix.":".$active_id.":is_lottery:".$uid.":".$today;
if($redis->get($imei_key) || $redis->get($user_key)){
	$is_lottery = 1;
}else{
	$is_lottery = 0;
}
$tplObj -> out['is_lottery'] = $is_lottery;

$award_all = get_award_record($active_id);
$tplObj -> out['award_all'] = $award_all;
//奖品展示
$tplObj -> out['prize_list'] = get_prize_list();
if($_GET['stop'] == 1){
	$tpl = "lottery/{$prefix}/{$tpl_prefix}_end.html";
}else{
	//指定用户中奖
	add_test_user_award();
	$tpl = "lottery/{$prefix}/{$tpl_prefix}_index.html";
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
$tplObj -> out['product'] = $_SESSION['product'];
$tplObj -> display($tpl);