<?php
include_once ('./fun.php');
session_begin($sid);
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/{$prefix}/index.php";
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
	"type_lottery" =>2,  //1:老虎机,2:转盘,3:九宫格
	"type_name" =>"转盘",
	'key' => 'lottery',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
if($_POST){
	$is_lottery_key = "{$prefix}:{$active_id}:is_lottery:".$uid;
	$is_lottery = $redis->get($is_lottery_key);
	$pid = $_POST['pid'];
	$level = $_POST['level'];
	if($is_lottery[$pid]){
		$array = array(
			'code' => 0,
			'msg' => '已领取请不要重复领取',
		);
		exit(json_encode($array));		
	}
	$prize_list = get_gift_list($level);
	load_helper('task');
	$task_client = get_task_client();
	$new_array=array(
		'uid'=>$uid,
		'aid'=>$active_id,
		'username'=>$_SESSION['USER_NAME'],
		'prefix'=>$prefix,
		'package' => $prize_list[$level]['desc'],
		'position' => $level
	);
	
	$the_award = $task_client->do('dota_sign', json_encode($new_array));	
	$lottery_rs = json_decode($the_award,true);
	//抽奖成功日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => $level ,
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"award_name" => $lottery_rs['prizename'],
		'key' => 'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	$arr = array(	
		'gift_number' => $lottery_rs['gift_number'],
		'uid' =>  $uid,
		'package' => $pkg,
		'softname' =>$lottery_rs['prizename'],
		'update_tm' =>$time,
		'time' => date("Y-m-d",$time)
	);		
	$award_key = "{$prefix}:{$active_id}_gift_prize:{$uid}";	
	$redis -> lPush($award_key,json_encode($arr),30*60);	
	$is_lottery[$pid] = 1;
	$redis->set($is_lottery_key,$is_lottery,60*86400);
	$prize_list[$level]['num'] = $prize_list[$level]['num']-1;	
	$key = "{$prefix}:{$active_id}:prize_list";
	$redis->set($key,$prize_list,60*86400);
	$array = array(
		'code' => 1,
		'prizename' => $lottery_rs['prizename'],
		'gift_number' => $lottery_rs['gift_number'],
	);
	exit(json_encode($array));
}

