<?php
include_once ('./fun.php');
session_begin($sid);
$url = "http://promotion.anzhi.com/lottery/{$prefix}/index.php?aid={$active_id}&sid={$sid}";
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
$tm_con = get_config($active_id,$uid);
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
if($tm_con[date('Y-m-d',$time)]['num']){	
	$tm_con[date('Y-m-d',$time)]['status'] = 1;
	$redis->set("{$prefix}:{$active_id}:tm_config:".$uid,$tm_con,86400*10);
}
exit(json_encode(array('code'=>1,'msg'=>'成功')));