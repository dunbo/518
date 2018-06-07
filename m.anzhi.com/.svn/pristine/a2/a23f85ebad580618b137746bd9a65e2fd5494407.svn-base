<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/".$prefix."/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;

$config = get_config($_GET['ap_id']);
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
//$_SESSION['USER_ID'] = 13176;
user_loging_new();
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
	brush_second_do($active_id);
	get_brush_bysn();
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
		if($config['free_day_switch'] == 1){
			//登录每天获得一次抽奖机会
			$login_num_key = "{$prefix}:".$active_id.":login_lottery_num:".$uid.":".$today;
			add_lottery_num($uid,$config['lottery_num_limit'],$login_num_key,86400,$config['like_limit']);
		}
		$lottery_num = user_lottery_num($uid);	
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;	
	$sign_config = sign_config($uid,$config['start_tm'],$config['like_limit']);
	list($free_retroactive_num,$azb_retroactive_num) = get_retroactive_num($uid,$config['share_add_all'],$config['rank_lottery_desc_pic']);
	$tplObj -> out['free_retroactive_num'] = $free_retroactive_num;	
	$tplObj -> out['azb_retroactive_num'] = $azb_retroactive_num;	
	$az_money = get_azmoney($uid);	
	//var_dump($az_money );
	// $sign_config['2017-02-20']['status'] = 0;
	// $redis->set("{$prefix}:{$active_id}:tm_config:".$uid, $sign_config, 86400*10);
	$tplObj -> out['az_money'] = $az_money['azmoney'] ? $az_money['azmoney'] : 0;
	$tplObj -> out['isHasPayPwd'] = $az_money['isHasPayPwd'];
	$tplObj -> out['con_azb'] = intval($config['rank_lottery_desc_text']);
	//用户每日获得抽奖次数限制
	$day_limit_key = "{$prefix}:".$active_id.":limit_lottery_num:".$uid.":".$today;
	$day_limit =  $redis->get($day_limit_key);
	if(intval($day_limit) >= intval($config['lottery_num_limit'])){
		$tplObj -> out['is_lottery_limit'] = 0;
	}else{
		$tplObj -> out['is_lottery_limit'] = 1;
	}		
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}
if($_GET['stop'] == 1){
	$tpl = "lottery/".$prefix."/end.html";
}else{
	$tplObj -> out['lottery_num'] = isset($lottery_num) ? $lottery_num : 1;
	$tpl = "lottery/".$prefix."/index.html";
	list($prize_name,$prize_level) = get_prize_name($active_id);
	$tplObj -> out['prize_results'] = $prize_level;
	$tplObj -> out['prize_limit'] = count($prize_name);	
}
//获取礼包礼券id---过滤礼包礼券
$pid_arr = get_virtual_pid($active_id,$prefix,'sign_prize');
$where = array(	
	'pid' => array('exp',"not in (".$pid_arr.")"),
	'status' => 1,
	'from' => 1,
	'aid' => $active_id,
);
$award_all = get_award_all_new($active_id, $prefix, 'sign_award',$where);
$tplObj -> out['award_all'] = $award_all;//获取获奖人信息奖品
$tplObj -> out['list'] = $config;
$img_host = getImageHost();
$prize_icon = get_sign_icon();
$tplObj -> out['prize_icon'] = $prize_icon;
$sign_config = sign_config($uid,$config['start_tm'],$config['like_limit']);
$tplObj -> out['sign_config'] = $sign_config;
$day =  $sign_config[$today]['level'];
if(!$day){
	if($time > $config['end_tm']){
		$day = $config['like_limit']+1;
	}else{
		$day = 0;
	}
}
$tplObj -> out['day'] = $day;
if($uid && $config['show_award'] != 3){
	$sign_days = get_sign_days($uid,$config['show_award'],$sign_config,$day);
	$tplObj -> out['sign_days'] = $sign_days ? $sign_days : 0;
}else{
	$tplObj -> out['sign_days'] =  0;
}
$receive_prize = get_receive_prize($config['download_bgcolor'],$uid);
$tplObj -> out['download_bgcolor'] = $receive_prize;

$tplObj -> out['img_url'] = $img_host;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['activity_host'] = $activity_host;
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);