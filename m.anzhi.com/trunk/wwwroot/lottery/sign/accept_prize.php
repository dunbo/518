<?php
include_once ('./fun.php');
$url = "{$activity_host}/lottery/{$prefix}/index.php?aid={$active_id}&sid={$sid}";
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	//已登录
	$uid = $_SESSION['USER_UID'];
}else {
	$_SESSION['USER_ID'] = 13176;
	//未登录 跳转到首页
	if($_POST) {
		exit(json_encode(array('code'=>2, 'url'=>$url)));
	}else {
		header("Location: {$url}");
	}
}
$level = $_POST['level'];
//用户领取日志
$log_data = array(
	'time'	=>	$time,
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'sid' => $sid,	
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	"DEVICE_SN" => $_SESSION['DEVICE_SN'],
	'activity_id'	=>	$active_id,
	'level'	=>	$level,
	'key'	=>	'receive'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

$config = get_config($_GET['ap_id']);

$receive_prize = get_receive_prize($config['download_bgcolor'],$uid);
if($receive_prize[$level] == 1){
	exit(json_encode(array('code'=>0,'msg'=>'您领取过了')));
}

$res = sign_prize($uid,$level,$config['acrivity_name']);
if($res['code'] == 0){
	exit(json_encode(array('code'=>0,'msg'=>$res['msg'])));
}

$receive_prize[$level] = 1;
$redis->set("{$prefix}:{$active_id}:receive_prize:".$uid, $receive_prize, 86400*60);
//用户领取成功日志
$log_data = array(
	'time'	=>	$time,
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	"DEVICE_SN" => $_SESSION['DEVICE_SN'],
	'activity_id'	=>	$active_id,
	'level'	=>	$level,	
	'type'	=>	$res['type'],	
	"award_name" => $res['prizename'],
	'key'	=>	'receive_success'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

//用户所有兑换奖品信息
$arr = array(	
	'uid'	=>	$uid,
	'type'	=>	$res['type'],
	'pid'	=> 	$res['prize_rank'],
	'prizename'	=>	$res['type'] == 5 ?  $res['softname'].":".$res['gift_number'] :$res['prizename'],
	'time'	=>	date("Y-m-d",$time)
);		
$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
$redis -> lPush($key,json_encode($arr), 30*60);	
$array = array(
	'code' => 1,
	'prizename' => $res['prizename'],
	'prize_rank' => $res['prize_rank'],
	'softname' => $res['softname'],
	'gift_number' => $res['gift_number'],
	'package' => $res['package'],
);
exit(json_encode($array));