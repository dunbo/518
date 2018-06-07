<?php
include_once ('./fun.php');
session_begin($sid);
$uid = $_SESSION['USER_UID'];	
$imsi = $_SESSION['USER_IMSI'];
$deviceid = $_SESSION['DEVICEID'];
$time = time();
//$time = get_now_time();
if($_POST['down']){
	$pkg = $_POST['pkgname'];
	$key  = "{$prefix}:{$active_id}_down_num_cun:{$pkg}:{$deviceid}";
	$num_day = $redis->get($key);
	if($num_day === null){
		$redis->set($key,1,86400*10);
		get_sign_down_share_num($prefix,$active_id,$uid,"down_status",$time);		
	}
	exit(json_encode(array('code' => 1)));
}else if($_POST['share']){
	//用户分享日志
	$log_data = array(
		'uid'=>$uid,
		'username' => $_SESSION['USER_NAME'],
		'imsi' => $imsi,
		'device_id' => $deviceid,
		'time' => $time,
		'activity_id' => $active_id,
		'key' => 'share'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
	$tm_config = get_config($prefix,$active_id,$uid);		
	if($tm_config[date('Y-m-d',$time)]['share_status'] == 1){
		$is_share = 1;//当天是否签到过(1是2否)
	}else{
		get_sign_down_share_num($prefix,$active_id,$uid,"share_status",$time);
		$is_share = 2;//当天是否签到过
	}	
	exit(json_encode(array('code' => 1,'is_share' => $is_share)));
}else if($_GET['soft_list'] == 1){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$h_str = 'dev.';
		$tplObj -> out['is_test'] = 1;
	}	
	$tpl = "lottery/{$prefix}_sign/soft_list.html";
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> display($tpl);	
}

