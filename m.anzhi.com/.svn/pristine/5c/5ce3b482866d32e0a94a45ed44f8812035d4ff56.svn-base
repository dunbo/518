<?php
include_once ('./xy2_fun.php');
$active_id = $_POST['aid'];
$sid = $_POST['sid'];
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/xy2/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}
$time = time();
//抽奖日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	"award_level" => '',//pid
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	"award_name" =>'',
	'key' => 'lottery'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
if($_POST){
	//剩余抽奖次数
	$now_num = $redis->setx('incr','xy2_rest_lottery_num'.$uid,-1);
	if($now_num < 0){
		$redis->set('xy2_rest_lottery_num'.$uid,0,10*60);
		exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
	}	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array();
	$new_array['uid'] =$uid;
	$new_array['username'] = $_SESSION['USER_NAME'];
	$the_award = $task_client->do('xy2', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);		
	if($lottery_rs['pid'] == 0){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
	}	
	//用户已用抽奖次数+1
	save_deduction_num($uid);
	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['pid'],//pid
			"user" => $_SESSION['USER_NAME'],
			"package" => $lottery_rs['pid'] ==0 ? $gift_info['package'] : '',
			"softname" => $lottery_rs['pid'] ==0 ? $gift_info['softname'] : '',
			"gift" =>  $lottery_rs['pid'] ==0 ? $gift_info['gift_number'] : '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['name'],
			'key' => 'lottery_success'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	if($lottery_rs['pid'] == 0){
		//用户中奖信息
		$arr = array(	
			'gift_number' => $gift_info['gift_number'],
			'uid' => $uid,
			'package' => $gift_info['package'] ,
			'softname' => $gift_info['softname'],
			'time' => date("Y-m-d",$time) ,
		);
		//礼包的所有兑换信息
		$redis -> lPush("xy2_gift_prize:{$uid}",json_encode($arr));	
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['pid'],
			'prizename' => $lottery_rs['name'],
			'time' => date("Y-m-d",$time)
		);			
		$redis -> lPush("xy2_draw_award:{$uid}",json_encode($arr));
	}
	$array = array(
		'code' => 1,
		'pid' => $lottery_rs['pid'],
		'softname' => $gift_info['softname'],
		'gift_num' => $gift_info['gift_number'],
		'prizename' => $lottery_rs['name'],
	);
	exit(json_encode($array));
}

