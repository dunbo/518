<?php
include_once ('./fun.php');
session_begin($sid);
$url = "{$activity_host}/lottery/{$prefix}/index.php?aid={$active_id}&sid={$sid}";
if(isset($_SESSION['USER_UID'])) {
	//已登录
	$uid = $_SESSION['USER_UID'];
}else {
	//未登录 跳转到首页
	if($_POST) {
		exit(json_encode(array('code'=>2, 'url'=>$url)));
	}else {
		header("Location: {$url}");
	}
}

$tm_con = get_config($active_id, $uid);
//用户签到日志
$log_data = array(
	'time'	=>	$time,
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	'activity_id'	=>	$active_id,
	'key'	=>	'sign'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

if($tm_con[date('Y-m-d',$time)]['status'] == 1){
	exit(json_encode(array('code'=>0,'msg'=>'您签到过了')));
}

if( empty($tm_con[date('Y-m-d',$time)]) ){
	exit(json_encode(array('code'=>0,'msg'=>'签到日期已过或未开始')));
}

//防刷处理
$timeKey	=	date('Y-m-d',$time);
$aollow_key	=	"{$prefix}:{$active_id}:user_sign:{$timeKey}:{$uid}";
$tomo		=	date("Y-m-d",strtotime("+1 day"));
$expire		=	strtotime($tomo) - $time;
$res		=	$redis -> setnx($aollow_key, 1, $expire);
if( $res === false ) {
	exit(json_encode(array('code'=>0,'msg'=>'您签到过了')));
}

if($tm_con[date('Y-m-d',$time)]['num']) {	
	$tm_con[date('Y-m-d',$time)]['status'] = 1;
	$redis->set("{$prefix}:{$active_id}_tm_config:".$uid, $tm_con, 86400*10);
}

$i = 0;
foreach($tm_con as $k => $v) {
	if($v['num'] && $v['status'] == 1) {
		$i++;
	}
}

$data = array(
	'draw_data_num' => $i > 5 ? 5 : $i,
	'uid' => $uid,
	'aid' => $active_id,
);

add_user($data,$time);

exit(json_encode(array('code'=>1,'msg'=>'成功')));