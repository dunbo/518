<?php
include_once ('./fun.php');

//首页日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	'product' => $_SESSION['product'], //1市场 13 sdk
	"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
	'key' => 'show_homepage',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

$build_query = http_build_query($_GET);
if($configs['is_test']){
	$h_str = 'dev.';
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;

$tplObj -> out['login_url'] = $login_url;

user_loging_new();

//$_SESSION['USER_ID'] = 13176;
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	unset($_COOKIE['_AZ_COOKIE_']);
	
	$uid = $_SESSION['USER_UID'];

	//每天免费3次抽奖机会【单用户或者单设备】
	$lottery_num_uid_key = $prefix.":".$active_id.":free_lottery_num:".$uid.":".$today;
	if(!$redis->exists($lottery_num_uid_key)){
		$res = $redis->set($lottery_num_uid_key, 3, 86400*10);
	}
	$lottery_num_uid = $redis->get($lottery_num_uid_key);

	$lottery_num_imei_key = $prefix.":".$active_id.":free_lottery_num:".$_SESSION['DEVICEID'].":".$today;
	if(!$redis->exists($lottery_num_imei_key)){
		$res = $redis->set($lottery_num_imei_key, 3, 86400*10);
	}
	$lottery_num_imei = $redis->get($lottery_num_imei_key);
	if($lottery_num_uid > $lottery_num_imei){
		$lottery_num = $lottery_num_imei;
	}else{
		$lottery_num = $lottery_num_uid;
	}
	$tplObj -> out['lottery_num'] = $lottery_num ? $lottery_num : 0;

	//免费抽奖倒计时
	$lottery_time_key = $prefix.":".$active_id.":free_lottery_time:".$uid.":".$today;
	if(!$redis->exists($lottery_time_key)){
		$lottery_time = 0;
	}else{
		$lottery_time = $redis->get($lottery_time_key);
	}
	$ttl_time = $lottery_time + 599 - $time; //倒计时时间
	if($ttl_time < 0){
		$ttl_time = 0;
	}
	$tplObj -> out['ttl_time'] = $ttl_time;

	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	$az_money = get_azmoney($uid);
	$tplObj -> out['az_money'] = $az_money['azmoney'] ? $az_money['azmoney'] : 0;
	$tplObj -> out['isHasPayPwd'] = $az_money['isHasPayPwd'];
}else {//未登录
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 3;
	$tplObj -> out['ttl_time'] = 0;
}

//跑马灯获奖人信息
$where = array(
	'status' => 1,
	'aid' => $active_id,
);
$award_all = get_award_record($active_id);

$tplObj -> out['award_all'] = $award_all;

if($stop == 1){
	$tpl = "lottery/{$prefix}/end.html";
}else{
	$tpl = "lottery/{$prefix}/index.html";
}

if(is_weixin() || $_GET['is_weixin']){
	$tpl = "lottery/vip/weixin.html";
}

$tplObj -> out['product'] = $_SESSION['product'];
$tplObj -> display($tpl);