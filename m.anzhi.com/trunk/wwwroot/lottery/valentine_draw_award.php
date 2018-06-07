<?php
include_once ('./valentine_fun.php');
//$active_id =312;
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	header("Location: http://promotion.anzhi.com/lottery/valentine.php");
}
if($_POST){	
	//剩余抽奖次数
	$rest_num = $redis->get('valentine_rest_num'.$uid);
	$now_num = $redis->setx('incr','valentine_rest_num'.$uid,-1);
	if($rest_num <= 0 || $now_num < 0){
		$redis->set('valentine_rest_num'.$uid,0,30*60);
		exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
	}	
	//用户已用抽奖次数+1
	save_deduction_num($uid);
	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array();
	$new_array['uid'] =$uid;
	$new_array['username'] = $_SESSION['USER_NAME'];
	if($_POST['pkg']){
		$new_array['package'] = $_POST['pkg'];	
	}	
	file_put_contents('/tmp/valentine.log',$new_array);
	$the_award = $task_client->do('valentine', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);		
	if($lottery_rs['pid'] == 0){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
	}	
	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => time(),
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
			'update_tm' => time(),
		);
		//礼包的所有兑换信息
		$redis -> lPush("valentine_gift_prize:{$uid}",json_encode($arr));	
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['pid'],
			'prizename' => $lottery_rs['name'],
			'time' => $time
		);			
		$redis -> lPush("valentine_kind_award:{$uid}",json_encode($arr));
	}
	$array = array(
		'code' => 1,
		'pid' => $lottery_rs['pid'],
		'softname' => $gift_info['softname'],
		'gift_num' => $gift_info['gift_number'],
		'prizename' => $lottery_rs['name'],
	);
	exit(json_encode($array));
}else{
	if($_GET['log'] == 1){
		//抽奖日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => time(),
			"award_level" => '',//pid
			"user" => $_SESSION['USER_NAME'],
			"package" => "",
			"gift" =>  '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['prizename'],
			'key' => 'lottery'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
		echo 1;
		exit;
	}
	$tplObj -> out['static_url'] = $configs['static_url'];
	//$tplObj -> out['imgurl'] = getImageHost();
	//剩余抽奖次数
	list($userinfo,$rest_num) =get_rest_valentine($uid);
	$tplObj -> out['valentine_rest_num'] = $rest_num;
	//最近的10条兑奖信息
	$top10_prize = get_top10_prize();
	$tplObj -> out['top10_prize'] = $top10_prize;	
	//用户礼包兑换信息
	$gift_prize_list = get_user_kind_gift($uid);
	if(!$gift_prize_list){
		$tplObj -> out['is_pkg'] = 1;//是否要传pkg
	}	
	//用户实物兑换信息
	$kind_award_list = get_user_kind_award($uid);
	if(!$gift_prize_list && !$kind_award_list){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;//有中奖信息	
	}
	$tplObj -> out['money'] = $userinfo['money'];	
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $sid;
	$tplObj -> display('lottery/valentine_draw_award.html');	
}

