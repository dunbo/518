<?php
include_once ('./fun_2017.php');
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
	'from_type' => $_POST['from_type'],//1免费砸蛋2一次砸蛋3十次砸蛋
	'key' => 'lottery',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	
$home_key_today = $prefix.":".$active_id.':home:'.$uid;
$home_time = $redis->get($home_key_today);
if($configs['is_test'] != 1 && ($home_time == $time)){
	exit;
}
if($_POST['from_type'] == 1){
	//是否有免费抽奖机会【针对设备】
	$imei_key = $prefix.":".$active_id.":is_lottery:".$_SESSION['DEVICEID'].":".$today;
	$is_lottery = $redis->get($imei_key);
	if($is_lottery == 1){
		exit(json_encode(array('code'=>0,'msg'=>"您今天免费砸蛋已经用过了！")));
	}
	$redis->set($imei_key,1,86400*10);
}else{
	$azb_con = array(
		2 => 20,
		3 => 2000
	);
	$azb_mount = $azb_con[$_POST['from_type']];
	//获取剩余安智币
	$res = get_azmoney($uid);
	if($res['azmoney'] < $azb_mount){
		exit(json_encode(array('code'=>0,'msg'=>"您的安智币余额不足，请在电脑端安智用户中心或联运游戏中进行充值；")));
	}	
	//消费安智币(必须先扣安智币除才能执行抽奖操作)
	$azb_res = azb_consume($uid,$_POST['pwd'],$azb_mount);
	if($azb_res['code'] == 0){
		exit(json_encode(array('code'=>0,'msg'=>$azb_res['msg'])));
	}	
}
save_deduction_num_new($uid,$active_id,$_SESSION['USER_NAME'],$prefix,'valentine_draw_userinfo');
save_used_money($uid,$azb_mount);

$lottery_res = lottery_do($uid,$_POST['from_type']);
$no_lottery = 0;
$succ_str = '';
$is_kind = 0;
foreach($lottery_res as $k => $v){
	if($v == -1){
		$no_lottery = 1;
		continue;
	}
	//抽奖成功日志
	$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"			=>	$_SERVER['REMOTE_ADDR'],
		"sid"			=>	$sid,
		"time"			=>	$time,
		"award_level"	=>	$v['prize_rank'],
		'type'=>$v['type'],//1实物2礼包3谢谢参与4礼券5礼包（直接发放）				
		"user"			=>	$_SESSION['USER_NAME'],
		'uid'			=>	$uid,
		"award_name"	=>	$v['prizename'],
		'from_type' => $_POST['from_type'],//1免费砸蛋2一次砸蛋3十次砸蛋		
		'key'			=>	'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));


	//用户所有兑换奖品信息
	$arr = array(	
		'uid'	=>	$uid,
		'type'	=>	$v['type'],
		'pid'	=> 	$v['prize_rank'],
		'prizename'	=>	$v['prizename'],
		'gift_number'	=>	$v['gift_number'],
		'time'	=>	date("Y-m-d",$time)
	);		
	$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
	$redis -> lPush($key,json_encode($arr), 30*60);
	$kk = $k+1;
	if($v['type'] == 1){
		$succ_str .= "<p>".$kk ."、恭喜您获得了<span>《".$v['prizename']."》</span>，请尽快完善个人信息，以免造成奖品无法发送 </p>";
		$is_kind = 1;
	}else if($v['type'] == 4){
		$succ_str .= "<p>".$kk ."、恭喜您获得了<span>《".$v['prizename']."》</span>，礼券有效期为3天，请尽快使用 </p>";
	}
}
if($succ_str != ''){
	$ret_arr = array(
		'code' => 1,
		'is_kind' => $is_kind,
		'msg' => $succ_str,
	);
	exit(json_encode($ret_arr));
}
if($no_lottery == 1){
	exit(json_encode(array('code'=>0,'msg'=>'未中奖')));
}