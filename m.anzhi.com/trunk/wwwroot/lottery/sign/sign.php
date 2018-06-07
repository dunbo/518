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
$config = get_config($_GET['ap_id']);
$tm_con = sign_config($uid,$config['start_tm'],$config['like_limit']);
//用户签到日志
$log_data = array(
	'time'	=>	$time,
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'sid'	=>	$sid,
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	"DEVICE_SN" => $_SESSION['DEVICE_SN'],
	'activity_id'	=>	$active_id,
	'key'	=>	'sign'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
$now_day = date('Y-m-d',$time);
if($tm_con[$now_day]['status'] == 1){
	exit(json_encode(array('code'=>0,'msg'=>'您本日已签到过')));
}

if( empty($tm_con[$now_day]) ){
	exit(json_encode(array('code'=>0,'msg'=>'签到日期已过或未开始')));
}

if($tm_con[$now_day]['level']) {	
	$tm_con[$now_day]['status'] = 1;
	$redis->set("{$prefix}:{$active_id}:tm_config:".$uid, $tm_con, 86400*60);
}
//添加用户补签数据
add_sign_data($uid,$now_day,1);
//签到成功给抽奖机会
if($config['get_lottery_type'] == 1){
	$sign_num_key = "{$prefix}:".$active_id.":sign_lottery_num:".$uid.":".$now_day;
	$ret = add_lottery_num($uid,$config['lottery_num_limit'],$sign_num_key,86400,$config['acrivity_day']);		
}
//用户签到日志
$log_data = array(
	'time'	=>	$time,
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'sid' => $sid,	
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	"DEVICE_SN" => $_SESSION['DEVICE_SN'],
	'activity_id'	=>	$active_id,
	'key'	=>	'sign_success'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
//签到即送
if($config['show_award'] == 3){
	$res = sign_prize($uid,$tm_con[$now_day]['level'],$config['acrivity_name']);
	if($res['code'] == 0){
		exit(json_encode(array('code'=>0,'msg'=>$res['msg'])));
	}
	//领取成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $tm_con[$now_day]['level'],//pid
			"user" => $_SESSION['USER_NAME'],
			"package" =>  $res['type'] == 5  ? $res['package'] : '',
			"softname" => $res['type'] == 5 ? $res['softname'] : '',
			"gift" => $res['type'] == 5 ? $res['gift_number'] : '',
			'uid'=>$uid,
			"type" =>$res['type'],	
			"award_name" => $res['prizename'],
			'key' => 'award'
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
}

brush_second_do($active_id);
$ret_arr = array(
	'code' => 1,
	'msg' => '签到成功',
	"softname" => $res['type'] == 5 ? $res['softname'] : '',
	"gift" => $res['type'] == 5 ? $res['gift_number'] : '',
	"type" =>$res['type'],	
	"prizename" => $res['prizename'],
);
if($config['show_award'] == 3){
	$ret_arr['msg'] = "恭喜您签到成功，获得《".$res['prizename']."》奖品";
}
exit(json_encode($ret_arr));