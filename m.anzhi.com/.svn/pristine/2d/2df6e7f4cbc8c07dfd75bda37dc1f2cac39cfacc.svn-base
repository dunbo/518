<?php
include_once ('./fun.php');
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = $activity_host."/lottery/{$prefix}/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}
//var_dump($_POST);exit;
//领取日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	"award_level" => '',
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	'key' => 'lottery',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	
$home_key_today = $prefix.":".$active_id.':home:'.$uid;
$home_time = $redis->get($home_key_today);
if($configs['is_test'] != 1 && ($home_time == $time)){
	exit;
}
$azb_mount = intval($_POST['azb_mount']);
if($azb_mount < 10){
	exit(json_encode(array('code'=>0,'msg'=>"您消费的安智币有误，请核对！")));
}
$config = get_prize_config(date("Ymd",$time),$time);
if($config[0]['azb_mount'] != $azb_mount){
	exit(json_encode(array('code'=>0,'msg'=>"您消费的安智币有误，请核对！(1)")));
}
//获取剩余安智币
$res = get_azmoney($uid);
if($res['azmoney'] < $azb_mount){
	exit(json_encode(array('code'=>0,'msg'=>"您的安智币余额不足，请在电脑端安智用户中心或联运游戏中进行充值；")));
}

$lottery_res = lottery_do($uid,$_POST['level_ids'],$gift_pkg);
if($lottery_res['code'] == 0){
	exit(json_encode(array('code'=>0,'msg'=>$lottery_res['msg'])));
}

//消费安智币
$azb_res = azb_consume($uid,$_POST['pwd'],$azb_mount);
if($azb_res['code'] == 0){
	exit(json_encode(array('code'=>0,'msg'=>$azb_res['msg'])));
}

//抽奖成功日志
$log_data = array(
	"imsi"			=>	$_SESSION['USER_IMSI'],
	"device_id"		=>	$_SESSION['DEVICEID'],
	"activity_id"	=>	$active_id,
	"ip"			=>	$_SERVER['REMOTE_ADDR'],
	"sid"			=>	$sid,
	"time"			=>	$time,
	"award_level"	=>	$lottery_res['prize_rank'],
	"user"			=>	$_SESSION['USER_NAME'],
	'uid'			=>	$uid,
	"award_name"	=>	$lottery_res['prizename'],
	'key'			=>	'award',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

get_prize_num($_POST['level_ids']);

//用户所有兑换奖品信息
$arr = array(	
	'uid'	=>	$uid,
	'type'	=>	$lottery_res['type'],
	'pid'	=> 	$lottery_res['prize_rank'],
	'prizename'	=>	$lottery_res['prizename'],
	'gift_number'	=>	$lottery_res['gift_number'],
	'time'	=>	date("Y-m-d",$time)
);		
$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
$redis -> lPush($key,json_encode($arr), 30*60);

exit(json_encode($lottery_res));