<?php
include_once ('./fun.php');

if($_POST['is_remind'] == 1){
	//签到提醒
	$ret = switch_remind($_POST['switch_remind'],$uid);	
	if($ret){
		$log_data = array(
			'time'	=>	$time,
			'imsi'	=>	$_SESSION['USER_IMSI'],
			'uid'	=>	$uid,
			'sid'	=>	$sid,
			'username'	=>	$_SESSION['USER_NAME'],
			'device_id'	=>	$_SESSION['DEVICEID'],
			"mac"	=>	$_SESSION['MAC'],
			"mid"	=>	$m_arr['id'],
			'switch_remind'=>$_POST['switch_remind'],//1开0关
			'key'	=>	'switch_remind'
		);
		permanentlog($log_key, json_encode($log_data));
		exit(json_encode(array('code'=>1,'msg'=>'成功')));	
	}else{	
		exit(json_encode(array('code'=>0,'msg'=>'失败')));	
	}
}

$sign_config = sign_config($uid);
//用户签到日志
$log_data = array(
	'time'	=>	$time,
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'sid'	=>	$sid,
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	"mac"	=>	$_SESSION['MAC'],
	"mid"	=>	$m_arr['id'],
	'key'	=>	'sign'
);
if($sign_config[$today]['status'] == 1){
	$log_data['msg'] = '您本日已签到过';
	permanentlog($log_key, json_encode($log_data));
	exit(json_encode(array('code'=>0,'msg'=>'您本日已签到过')));
}
if( empty($sign_config[$today]) ){
	$log_data['msg'] = '签到日期已过或未开始';
	permanentlog($log_key, json_encode($log_data));	
	exit(json_encode(array('code'=>0,'msg'=>'签到日期已过或未开始')));
}

// if($sign_config[$today]['level']) {	
	// $sign_config[$today]['status'] = 1;
	// $sign_redis->set("{$prefix}:{$m_arr['id']}:tm_config:".$uid, $sign_config, 86400*60);
// }
/*
//添加用户补签数据
add_sign_data($uid,$today,1);
//用户签到日志
$log_data = array(
	'time'	=>	$time,
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'sid' => $sid,	
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	"mid"	=>	$m_arr['id'],
	'key'	=>	'sign_success'
);
permanentlog($log_key, json_encode($log_data));
*/
//当天红包是否有效
$red_status_key = "{$prefix}:{$m_arr['id']}:{$sign_config[$today]['redid']}:now_red_id_status:".$today;
//@1:已抢完
$now_red_status = $redis->get($red_status_key);
if($sign_config[$today]['package']){
	//验证是否做过该任务
	$ishasdone_res = task_ishasdone($sign_config[$today]['task_type'],$sign_config[$today]['package'],$_SESSION['USER_ID']);
	if($ishasdone_res['code'] == 0){
		$log_data['msg'] = $ishasdone_res['msg'];
		permanentlog($log_key, json_encode($log_data));			
		exit(json_encode(array('code'=>0,'msg'=>$ishasdone_res['msg'])));
	}
}

//根据设备过滤红包（如A用户B用户在同一设备上预拆，B用户将无法拆红包）预拆红包
if($sign_config[$today]['package']){
	$is_pre_red = isExistsBillByDevice($sign_config[$today]['redid'],$_SESSION['DEVICEID'],$_SESSION['MAC'],1);
	if(!$is_pre_red){
		$is_pre_red = isExistsBillByDevice($sign_config[$today]['redid'],$_SESSION['DEVICEID'],$_SESSION['MAC'],0);
	}
}else{
	$is_pre_red = isExistsBillByDevice($sign_config[$today]['redid'],$_SESSION['DEVICEID'],$_SESSION['MAC'],0);
}
if($sign_config[$today]['type'] == 0 || $now_red_status || $ishasdone_res['ishasdone'] != false || $is_pre_red) {
	$is_lottery_key = "{$prefix}:{$m_arr['id']}:is_lottery:".$uid.":".$today;
	$redis->set($is_lottery_key,1,86400);
	$type = 0;
	if($sign_config[$today]['type'] == 0){
		$log_data['msg'] = "【变成已签到】因当天没配置抽奖或拆红包";
	}else if($now_red_status){
		$log_data['msg'] = "【变成已签到】红包已抢完,红包ID:".$sign_config[$today]['redid'];
	}else if($ishasdone_res['ishasdone'] != false){
		$log_data['msg'] = "【变成已签到】该任务已做过,红包ID:".$sign_config[$today]['redid'];
	}else if($is_pre_red){
		$log_data['msg'] = "【变成已签到】该设备已做过,红包ID".$sign_config[$today]['redid'];
	}
}else{
	$type = $sign_config[$today]['type'];
}
permanentlog($log_key, json_encode($log_data));		
$ret_arr = array(
	'code' => 1,
	'msg' => '签到成功',
	"type" => $type,	
	'did' =>$sign_config[$today]['did'],	
	'uid' =>$uid,	
);
exit(json_encode($ret_arr));