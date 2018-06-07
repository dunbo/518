<?php
include_once ('./fun.php');
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = $activity_host."/lottery/{$prefix}/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}
$num = intval($_POST['num']);
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
	'num'=>$num,
	"award_name" =>'',
	"type_lottery" => 1,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换		
	'key' => 'lottery',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
//剩余抽奖次数
$lottery_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
$now_num = $redis->setx('incr',$lottery_key,-$num);
if($now_num < 0){
	$redis->set($lottery_key,0,30*60);
	exit(json_encode(array('code'=>0,'msg'=>'可用锤子不足')));
}	
load_helper('task');
$task_client = get_task_client();
$new_array=array(
	'uid'=>$uid,
	'aid'=>$active_id,
	'username'=>$_SESSION['USER_NAME'],
	'prefix'=>$prefix,
);
$luk_arr = array(
	3 => 1,
	4 => 3,
	5 => 10,
	6 => 20,
	7 => 50,
	8 => 100,
);	
$gift_arr = array();
$award_arr = array();
$luk_num = 0;
for($i=1; $i <= $num; $i++ ){
	$new_array['package'] = get_gift_pkg($active_id,$uid,$_POST['pkg'],$prefix);	
	$the_award = $task_client->do('custom_lottery', json_encode($new_array));	
	$lottery_rs = json_decode($the_award,true);
	if($lottery_rs['prize_rank'] == 2){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
		del_gift_pkg($active_id,$uid,$gift_info['package'],$prefix);
	}	
	if($the_award == -1){
	//	exit(json_encode(array('code'=>1,'pid'=>-1,'msg'=>'抱歉，您本次暂未中奖，祝您下次抽奖好运临门！加油哦！','title'=>"未中奖")));
	}	
	
	//抽奖成功日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => $lottery_rs['prize_rank']  ,
		"user" => $_SESSION['USER_NAME'],
		"package" =>  $lottery_rs['prize_rank'] ==2 ? $gift_info['package'] : '',
		"softname" => $lottery_rs['prize_rank'] ==2 ? $gift_info['softname'] : '',
		"gift" =>  $lottery_rs['prize_rank'] ==2 ? $gift_info['gift_number'] : '',
		'uid'=>$uid,
		"award_name" => $lottery_rs['prize_rank'] ==2 ? "礼包" : $lottery_rs['prizename'],
		"type_lottery" => 1,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换		
		'key' => 'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	if($lottery_rs['prize_rank'] == 2){
		if(!$gift_info['gift_number']) return false;
		//用户中奖信息
		$arr = array(	
			'gift_number' => $gift_info['gift_number'],
			'uid' => $uid,
			'package' => $gift_info['package'] ,
			'softname' => $gift_info['softname'],
			'pid' =>  $lottery_rs['pid'],
			'time' => date("Y-m-d",$time) ,
		);
		if($lottery_rs['pid']){
			//礼包的所有兑换信息
			$key = "{$prefix}:{$active_id}_gift_prize:{$uid}";
			$redis -> lPush($key,json_encode($arr),30*60);	
			$gift_arr[] = $arr;
		}
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['pid'],
			'prizename' => $lottery_rs['prizename'],
			'time' => date("Y-m-d",$time)
		);	
		if($lottery_rs['pid']){	
			$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
			$redis -> lPush($key,json_encode($arr),30*60);
			$award_arr[] = $arr;
			if($lottery_rs['prize_rank'] > 2){
				$luk_num = $luk_num+$luk_arr[$lottery_rs['prize_rank']];
			}
		}
	}	
}

//我的幸运值
$luk_key = "{$prefix}:".$active_id.":luk:".$uid;
$redis->setx('incr',$luk_key,$luk_num);	
//用户已用抽奖次数
save_deduction_num($uid,$num);
$lottery_gift_key = "{$prefix}:{$active_id}_gift_prize:{$uid}:tmp";
$redis -> set($lottery_gift_key,$gift_arr,30*60);
$lottery_award_key = "{$prefix}:{$active_id}_draw_award:{$uid}:tmp";
$redis -> set($lottery_award_key,$award_arr,30*60);
if(!$gift_arr && !$award_arr){
	$array = array(	'code' => 0,'msg'=>'未中奖');	
}else{
	$array = array(	'code' => 1);
}
exit(json_encode($array));	
