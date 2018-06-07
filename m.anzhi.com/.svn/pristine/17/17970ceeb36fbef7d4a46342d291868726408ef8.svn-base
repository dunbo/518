<?php
include_once ('./fun.php');
session_begin($sid);
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/{$prefix}_sign/index.php?aid={$active_id}&sid={$sid}";
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
$tm_con = get_config($prefix,$active_id,$uid);
$time = time();
//$time = get_now_time();
//用户签到日志
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
get_sign_down_share_num($prefix,$active_id,$uid,"sign_status",$time);	
exit(json_encode(array('code'=>1,'msg'=>'成功')));