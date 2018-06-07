<?php
include_once ('./fun.php');
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['is_login'] = 1;	
}else{//未登录 跳转到首页
	$_SESSION['USER_ID'] = 13176;
	$url = $activity_host."/lottery/".$prefix."/index.php?aid={$active_id}&sid={$sid}";
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
	
if($_POST){
	$config = get_config();	
	$lottery_style_arr = array(
		1 => "老虎机",
		2 => "大转盘",
		3 => "九宫格",
	);
	//抽奖日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"DEVICE_SN" => $_SESSION['DEVICE_SN'],
		"product" => $_SESSION['product'],//1市场 13 sdk
		"MAC" => $_SESSION['MAC'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => '',//pid
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"type_lottery" =>$config['lottery_style'],
		"type_name" =>$lottery_style_arr[$config['lottery_style']],
		"award_name" =>'',
		'key' => 'lottery'
	);
	$lottery_num = user_lottery_num($uid);	
	sleep(1);
	//剩余抽奖次数
	$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$now_num = $redis->setx('incr',$lottery_num_key,-1);
	if($now_num < 0){
		$log_data['msg'] = '剩余抽奖次数不足';
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
		$redis->set($lottery_num_key,0,10*60);
		exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
	}	
	//刷出来的次数 抽奖直接不中
	$brush_res = get_brush_all($active_id,2);
	//获取谢谢参与等级
	$no_win_level = get_no_win_level($active_id,$prefix,'sign_prize');	
	if($brush_res['code'] == 0){ 
		$log_data['msg'] = $brush_res['msg'];
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
		//用户已用抽奖次数+1
		save_deduction_num_new($uid,$active_id,$_SESSION['USER_NAME'],$prefix,'sign_userinfo');	
		$array = array('code' => 1,'type'=>-1,'msg'=>$config['lost_no_desc'],'prize_rank'=>$no_win_level);
		exit(json_encode($array));	
	}else{
		load_helper('task');
		$task_client = get_task_client();
		$new_array = array();
		$new_array['uid'] =$uid;
		$new_array['aid'] =$active_id;
		$new_array['username'] = $_SESSION['USER_NAME'];
		$new_array['prefix'] = $prefix ;
		$new_array['activityName'] = $config['acrivity_name'];
		$new_array['table'] = "sign_prize";
		$new_array['table_award'] = "sign_award";	
		//是否限制用户不重复中同一款游戏礼包
		$the_award = $task_client->do('activity_lottery', json_encode($new_array));
		$lottery_rs = json_decode($the_award,true);		
		//用户已用抽奖次数+1
		save_deduction_num_new($uid,$active_id,$_SESSION['USER_NAME'],$prefix,'sign_userinfo');	
		if($the_award == -1 || $lottery_rs['code'] == 0){//未中奖
			$log_data['msg'] = $the_award == -1 ? '未中奖' : $lottery_rs['msg'] ;
			permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
			$array = array('code' => 1,'type'=>-1,'msg'=>$config['lost_no_desc'],'prize_rank'=>$no_win_level);
			exit(json_encode($array));	
		}			
	}
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['prize_rank'],//pid
			"user" => $_SESSION['USER_NAME'],
			"package" =>  $lottery_rs['type'] == 5  ? $lottery_rs['package'] : '',
			"softname" => $lottery_rs['type'] == 5 ? $lottery_rs['softname'] : '',
			"gift" => $lottery_rs['type'] == 5 ? $lottery_rs['gift_number'] : '',
			'uid'=>$uid,
			'type'=>$lottery_rs['type'],//1实物2礼包3谢谢参与4礼券5礼包（直接发放）			
			"type_lottery" =>$config['lottery_style'],
			"type_name" =>$lottery_style_arr[$config['lottery_style']],			
			"award_name" => $lottery_rs['prizename'],
			'key' => 'award'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	//所有兑换信息
	$arr = array(	
		'uid' => $uid,
		'pid' =>  $lottery_rs['pid'],
		'prizename' => $lottery_rs['type'] == 5 ? $lottery_rs['softname'].":".$lottery_rs['gift_number'] : $lottery_rs['prizename'],
		'time' => date("Y-m-d",$time)
	);		
	$award_key = "{$prefix}:{$active_id}_draw_award:{$uid}";	
	$redis -> lPush($award_key,json_encode($arr),30*60);
	if($lottery_rs['type'] !=2 && $lottery_rs['type'] !=5){
		set_brush_byip($active_id);
	}	
	$array = array(
		'code' => 1,
		'prizename' => $lottery_rs['prizename'],
		'prize_rank' => $lottery_rs['prize_rank'],
		'softname' => $lottery_rs['softname'],
		'gift_number' => $lottery_rs['gift_number'],
		'package' => $lottery_rs['package'],
	);
	exit(json_encode($array));
}

