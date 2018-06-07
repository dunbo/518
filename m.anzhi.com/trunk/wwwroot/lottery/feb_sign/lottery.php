<?php
include_once ('./fun.php');
session_begin($sid);
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/{$prefix}_sign/index.php?aid={$active_id}";
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
	$now_num = $redis->setx('incr',"{$prefix}:{$active_id}_rest_lottery_num:".$uid,-1);
	if($now_num < 0){
		$redis->set("{$prefix}:{$active_id}_rest_lottery_num:".$uid,0,30*60);
		exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
	}	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'table' => 'valentine_draw_prize',
		'table_gift' => 'valentine_draw_gift',
		'table_award' => 'valentine_draw_award',	
		'prefix' => "{$prefix}",	
	);	
	$new_array['package'] = get_gift_pkg($active_id,$uid,$_POST['pkg'],"{$prefix}");
	$the_award = $task_client->do('sign', json_encode($new_array));	
	$lottery_rs = json_decode($the_award,true);		
	if($lottery_rs['pid'] == 0){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
		del_gift_pkg($active_id,$uid,$gift_info['package'],"{$prefix}");
	}	
	//用户已用抽奖次数+1
	save_deduction_num_new($uid,$active_id,$_SESSION['USER_NAME'],"{$prefix}","valentine_draw_userinfo");
	if($the_award == -1){
		exit(json_encode(array('code'=>1,'pid'=>-1,'msg'=>'抱歉，您本次暂未中奖，祝您下次抽奖好运临门！加油哦！','title'=>"未中奖")));
	}		
	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['pid'] == 0 ? 0 : $lottery_rs['prize_rank']  ,
			"user" => $_SESSION['USER_NAME'],
			"package" => $lottery_rs['pid'] ==0 ? $gift_info['package'] : '',
			"softname" => $lottery_rs['pid'] ==0 ? $gift_info['softname'] : '',
			"gift" =>  $lottery_rs['pid'] ==0 ? $gift_info['gift_number'] : '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['pid'] ==0 ? "礼包" : $lottery_rs['prizename'],
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
		$key = "{$prefix}:{$active_id}_gift_prize:{$uid}";
		$redis -> lPush($key,json_encode($arr),86400*10);	
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['prize_rank'],
			'prizename' => $lottery_rs['prizename'],
			'time' => date("Y-m-d",$time)
		);		
		$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
		$redis -> lPush($key,json_encode($arr),86400*10);
		$arr['username'] = str_replace_cn_new($_SESSION['USER_NAME'], 1, -2 );
		$award_key = "{$prefix}:{$active_id}_draw_award";
		$redis -> lPush($award_key,json_encode($arr),86400*10);	
	}
	$array = array(
		'code' => 1,
		'pid' => $lottery_rs['pid'] == 0 ? 0 : $lottery_rs['prize_rank']  ,
		'package' => $gift_info['package'],
		'softname' => $gift_info['softname'],
		'gift_num' => $gift_info['gift_number'],
		'prizename' => $lottery_rs['prizename'],
	);
	exit(json_encode($array));
}

