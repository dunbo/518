<?php
include_once ('./fun.php');
session_begin();
$url = "http://promotion.anzhi.com/lottery/peak_warship/index.php?aid={$active_id}&sid={$sid}";
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
	'key' => 'click'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
if ($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176) 
{ //是否为登录
	$res = $redis->get("peak_warship:{$active_id}_is_sign:".$uid);
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
		$redis->set("peak_warship:{$active_id}_is_sign:".$uid,1,86400*30);
		echo 1;
	}
}
 else {
	//无登录
	echo 2;
}
