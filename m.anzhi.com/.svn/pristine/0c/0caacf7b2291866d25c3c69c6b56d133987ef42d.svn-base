<?php
include_once ('./fun.php');
session_begin();
$time = time();
$uid = $_SESSION['USER_UID'];
//用户点击
$log_data = array(
	'uid'=>$uid,
	'username' => $_SESSION['USER_NAME'],
	'imsi' => $_SESSION['USER_IMSI'],
	'device_id' => $_SESSION['DEVICEID'],
	'time' => $time,
	'activity_id' => $active_id,
	'key' => 'click_pre'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
$cook_is_jump = $_COOKIE['IS_JUMP'];//端外跳转
if (($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176)||($_SESSION['USER_IMSI'] ==''&&$cook_is_jump==1)) 
{ //是否为登录
	$res = $redis->get("my_name_MT3_new:{$active_id}_is_sign:".$uid);
	if($res==1){
		echo 1;
	}
	else
	{
		//用户预约日志
		$log_data = array(
			'uid'=>$uid,
			'username' => $_SESSION['USER_NAME'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'time' => $time,
			'activity_id' => $active_id,
			'key' => 'sign'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
		$redis->set("my_name_MT3_new:{$active_id}_is_sign:".$uid,1,86400*30);
		setcookie('IS_JUMP', '1', time()+24 * 3600 * 15, '/', 'anzhi.com');
		echo 1;
	}
}
 else {
	//无登录
	echo 2;
}
