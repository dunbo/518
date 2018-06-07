<?php
include_once ('./fun.php');
session_begin($sid);
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/ghost_sign_lottery/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}

$prefix='ghost_sign_lottery';
$custom = 'custom';
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
	"type_lottery" =>3,  //1:老虎机,2:转盘,3:九宫格
	"type_name" =>"九宫格",
	'key' => 'lottery',
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
	$new_array=array(
		'uid'=>$uid,
		'aid'=>$active_id,
		'username'=>$_SESSION['USER_NAME'],
		'prefix'=>$prefix,
		'package' =>$_POST['pkg'],
	);
	$the_award = $task_client->do('custom_lottery', json_encode($new_array));
	
	
	//用户已用抽奖次数+1
	save_deduction_num_new($uid,$active_id,$_SESSION['USER_NAME'],"{$prefix}","valentine_draw_userinfo");
	if($the_award == -1){
		/*exit(json_encode(array('code'=>1,'pid'=>-1,'msg'=>'抱歉，您本次暂未中奖，祝您下次抽奖好运临门！加油哦！','title'=>"未中奖")));*/
		//没中奖也给礼券
		$lottery_rs['prize_rank']=8;
		$lottery_rs['prizename']="5元礼券";
	}
	else
	{
		$lottery_rs = json_decode($the_award,true);
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
			'uid'=>$uid,
			"award_name" => $lottery_rs['prizename'],
			"type_lottery" =>3,
			"type_name" =>"九宫格",
			'key' => 'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	
	//实物的所有兑换信息
	$arr = array(	
		'uid' => $uid,
		'pid' =>  $lottery_rs['prize_rank'],
		'prizename' => $lottery_rs['prizename'],
		'time' => date("Y-m-d",$time)
	);		
	$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
	$redis -> lPush($key,json_encode($arr));
	$redis -> expire($key,86400*10);
	$arr['username'] = str_replace_cn_new($_SESSION['USER_NAME'], 1, -2 );
	$award_key = "{$prefix}:{$active_id}_draw_award";
	$redis -> lPush($award_key,json_encode($arr));	
	$redis -> expire($award_key,86400*10);
	
	$array = array(
		'code' => 1,
		'pid' => $lottery_rs['prize_rank'],
		'prizename' => $lottery_rs['prizename'],
	);
	exit(json_encode($array));
}

