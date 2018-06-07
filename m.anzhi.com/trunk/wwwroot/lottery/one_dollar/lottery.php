<?php
include_once ('./one_dollar_fun.php');
$active_id = $_POST['aid'];
$sid = $_POST['sid'];
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/one_dollar/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}
$time = time();
$prize_info = get_prize($_POST['position']);
//抢夺日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	"award_level" => $prize_info['id'],//pid
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	"award_name" => $prize_info['prizename'],
	"start_time" => $prize_info['start_time'],//抢夺开始时间
	'key' => 'lottery'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
if($_POST){
	$rest_integral = get_rest_integral($uid);
	if($rest_integral <= 0){
		exit(json_encode(array('code'=>0,'msg'=>'您当前积分不足，请您充值后再抢夺')));
	} 		
	if($prize_info['start_time'] > $time){
		exit(json_encode(array('code'=>0,'msg'=>$prize_info['prizename'].'抢夺未开始')));
	} 		
	if($prize_info['num'] == 0){
		exit(json_encode(array('code'=>0,'msg'=>'该奖品已经售馨')));
	} 		

	load_helper('task');
	$task_client = get_task_client();
	$new_array = array(
		'uid' => $uid,
		'username' => $_SESSION['USER_NAME'],
		'start_time' => $prize_info['start_time'],
		'pid' => $prize_info['id'],
		'prize_integral' => $prize_info['prize_integral'],
		'prizename' => $prize_info['prizename'],
		'num' => $_POST['num'],
		'lottery_chances' => $_POST['lottery_chances'],
		'position' => $_POST['position'],
	);
	$the_award = $task_client->do('one_dollar_worker', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);	
	if($lottery_rs['code'] == 0 ){
		exit($the_award);
	}
	//兑换成功日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => $lottery_rs['pid'],//pid
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"award_name" => $prize_info['prizename'],
		'key' => 'lottery_success'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	if($lottery_rs['code'] == 1 && $lottery_rs['status'] == 1 ){
		//中奖日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['pid'],//pid
			"user" => $_SESSION['USER_NAME'],
			'uid'=>$lottery_rs['uid'],
			"award_name" => $prize_info['prizename'],
			'key' => 'lottery_success_user'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
	}	
	exit(json_encode(array('code'=>1,'msg'=> $prize_info['prizename'],'num'=>$lottery_rs['num'])));
}

