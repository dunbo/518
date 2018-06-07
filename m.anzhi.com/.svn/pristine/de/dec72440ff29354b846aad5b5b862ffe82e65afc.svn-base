<?php
include_once ('./201803_fun.php');

$return = array();

if($_SESSION['USER_UID']){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = $activity_host."/lottery/{$prefix}/{$tpl_prefix}_index.php";
	$return['code'] = 2;
	$return['url'] = $url;
	echo json_encode($return);
	exit;
}

if($_POST['from_type'] == 1){
	//是否有免费抽奖机会【单用户单设备】
	$lottery_num_uid_key = $prefix.":".$active_id.":free_lottery_num:".$uid.":".$today;
	if(!$redis->exists($lottery_num_uid_key)){
		$res = $redis->set($lottery_num_uid_key, $free_lottery_num, 86400*10);
	}
	$lottery_num_uid = $redis->get($lottery_num_uid_key);

	$lottery_num_imei_key = $prefix.":".$active_id.":free_lottery_num:".$_SESSION['DEVICEID'].":".$today;
	if(!$redis->exists($lottery_num_imei_key)){
		$res = $redis->set($lottery_num_imei_key, $free_lottery_num, 86400*10);
	}
	$lottery_num_imei = $redis->get($lottery_num_imei_key);
	if($lottery_num_uid<1 || $lottery_num_imei<1){
		$return['code'] = 0;
		$return['msg'] = "您今天免费开宝箱次数已经用完了！";
		echo json_encode($return);
		exit;
	}else{
		//处理免费抽奖次数
		$redis->setx('incr', $lottery_num_uid_key, -1);
		$redis->setx('incr', $lottery_num_imei_key, -1);
		//记录免费抽奖时间
		$lottery_time_key = $prefix.":".$active_id.":free_lottery_time:".$uid.":".$today;
		$redis->set($lottery_time_key, $time, 86400*10);
	}
}else{
	$azb_con = array(
		2 => 50,
		//3 => 200,
		//4 => 500,
	);
	$azb_mount = $azb_con[$_POST['from_type']];
	//获取剩余安智币
	$res = get_azmoney($uid);
	if($res['azmoney'] < $azb_mount){
		$return['code'] = 0;
		$return['msg'] = "您的安智币余额不足，请在电脑端安智用户中心或联运游戏中进行充值！";
		echo json_encode($return);
		exit;
	}
	// 消费安智币(必须先扣安智币除才能执行抽奖操作)
	$azb_res = azb_consume($uid, $_POST['pwd'], $azb_mount);
	if($azb_res['code'] == 0){
		$return['code'] = 0;
		$return['msg'] = $azb_res['msg'];
		echo json_encode($return);
		exit;
	}
}

//抽奖日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	"award_level" => '',
	"user" => $_SESSION['USER_NAME'],
	'uid'=> $uid,
	// 1,免费 2,50安智币
	'from_type' => $_POST['from_type'],
	'key' => 'lottery',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
save_deduction_num_new($uid, $active_id, $_SESSION['USER_NAME'], $prefix, 'valentine_draw_userinfo');
save_used_money($uid, $azb_mount);
//抽奖开始
$lottery_res = lottery_do($uid, $_POST['from_type'], $azb_mount);
$no_lottery = 0;
$is_kind = 0; //是否实物中奖 0,不是 1,是
$succ_str = '';
$err_str = '';
if(!empty($lottery_res)){
	foreach($lottery_res as $k => $v){
		if($v == -1){
			$no_lottery = 1;
			continue;
		}
		if($v['code'] == 0){
			$err_str = $v['msg'];
			continue;
		}
		//中奖日志
		$log_data = array(
			"imsi"			=>	$_SESSION['USER_IMSI'],
			"device_id"		=>	$_SESSION['DEVICEID'],
			"activity_id"	=>	$active_id,
			"ip"			=>	$_SERVER['REMOTE_ADDR'],
			"sid"			=>	$sid,
			"time"			=>	$time,
			"award_level"	=>	$v['prize_rank'],
			'type'			=>	$v['type'], //1实物2礼包3谢谢参与4礼券5礼包(直接发放)6礼包(无礼包码)
			"user"			=>	$_SESSION['USER_NAME'],
			'uid'			=>	$uid,
			"award_name"	=>	$v['prizename'],
			'from_type' 	=> 	$_POST['from_type'],
			'key'			=>	'award',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

		//用户所有兑换奖品信息
		$arr = array(
			'uid'		=>	$uid,
			'type'		=>	$v['type'],
			'pid'		=> 	$v['prize_rank'],
			'prizename'	=>	$v['prizename'],
			'time'		=>	$time,
		);
		$key = "{$prefix}:{$active_id}_draw_award:{$uid}";
		if(!$redis->exists($key)){
			get_user_kind_award_list($uid, $active_id);
		}else{
			$redis->lPush($key, json_encode($arr), 30*60);
		}
		if($v['type'] == 1){
			$is_kind = 1;
		}
		$succ_str .= '《'.$v['prizename'].'》';
	}
}else{
	$no_lottery = 1;
}

if($succ_str != ''){
	$return['is_kind'] = $is_kind;
	$return['code'] = 1;
	$return['msg'] = $succ_str;
	echo json_encode($return);
	exit;
}
if($no_lottery == 1){
	$return['code'] = 3;
	$return['msg'] = '未中奖';
	echo json_encode($return);
	exit;
}
if($err_str != ''){
	$return['code'] = 4;
	$return['msg'] = $err_str;
	echo json_encode($return);
	exit;
}