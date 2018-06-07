<?php
include_once ('./fun.php');
session_begin($sid);
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['is_login'] = 1;	
}else{//未登录 跳转到首页
	$url = $configs['activity_url']."lottery/ranking/index.php?aid={$active_id}&sid={$sid}";
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
if($_POST){
	list($ranking_config,$activity_arr) = get_config($active_id);	
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
		"type_lottery" =>$ranking_config['lottery_style'],
		"type_name" =>$lottery_style_arr[$ranking_config['lottery_style']],
		"award_name" =>'',
		'key' => 'lottery'
	);
	//剩余抽奖次数
	$now_num = $redis->setx('incr','ranking_rest_lottery_num'.$uid.$active_id,-1);
	//$now_num = 1;//todo
	if($now_num < 0){
		$log_data['msg'] = '剩余抽奖次数不足';
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		$redis->set('ranking_rest_lottery_num'.$uid.$active_id,0,10*60);
		exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
	}	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array();
	$new_array['uid'] =$uid;
	$new_array['aid'] =$active_id;
	$new_array['activityName'] =$activity_arr['name'];
	$new_array['imsi'] = $_SESSION['USER_IMSI'];
	$new_array['username'] = $_SESSION['USER_NAME'];
	$new_array['lottery_num'] = 1;
	$the_award = $task_client->do('ranking_lottery', json_encode($new_array));
	$lottery_res = json_decode($the_award,true);	
	$lottery_rs = $lottery_res[0]; 
	//用户已用抽奖次数+1
	save_deduction_num($uid,$active_id,1);
	//获取谢谢参与等级
	$no_win_level = get_no_win_level($active_id,$prefix,'gm_lottery_prize');	
	if($lottery_rs == -1){//未中奖
		$log_data['msg'] = '未中奖';
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		$array = array(
			'code' => 1,
			'type' => -1,
			'pid' => $no_win_level,
		);
		exit(json_encode($array));	
	}	
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	

	//抽奖成功日志
	$log_data = array(
			"imsi" => $lottery_rs['imsi'],
			"device_id" => $_SESSION['DEVICEID'],
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],
			"MAC" => $_SESSION['MAC'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['level'],//pid
			"user" => $_SESSION['USER_NAME'],
			"package" =>  ($lottery_rs['type'] ==2||$lottery_rs['type'] ==5)  ? $lottery_rs['package'] : '',
			"softname" => ($lottery_rs['type'] ==2||$lottery_rs['type'] ==5) ? $lottery_rs['softname'] : '',
			"gift" => ($lottery_rs['type'] ==2||$lottery_rs['type'] ==5)  ? $lottery_rs['gift_number'] : '',
			'uid'=>$uid,
			'type'=>$lottery_rs['type'],//1实物2礼包3谢谢参与4礼券5礼包（直接发放）
			"type_lottery" =>$ranking_config['lottery_style'],
			"type_name" =>$lottery_style_arr[$ranking_config['lottery_style']],			
			"award_name" => $lottery_rs['name'],
			'key' => 'award'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	if( $lottery_rs['type'] ==2){
		//用户中奖信息
		$arr = array(	
			'first_text' => $lottery_rs['gift_number'],
			'uid' => $uid,
			'third_text' => $lottery_rs['package'] ,
			'second_text' => $lottery_rs['softname'],
		);
		//礼包的所有兑换信息
		$redis -> lPush("ranking_gift_prize{$uid}{$active_id}",json_encode($arr),1800);	
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['pid'],
			'prizename_old' => $lottery_rs['name'],
			'type' => $lottery_rs['type'],
			'time' => date("Y-m-d",$time)
		);			
		if($lottery_rs['type'] ==5){
			$arr['gift_code'] = $lottery_rs['gift_number'];
			$arr['prizename'] = $lottery_rs['softname'];
		}			
		$redis -> lPush("ranking_draw_award{$uid}{$active_id}",json_encode($arr),1800);
	}
	list($prize_name,$new_prize_level) = get_prize_name($active_id);
	$array = array(
		'code' => 1,
		'type' => $lottery_rs['type'],
		'prizename' => $lottery_rs['name'],
		'pid' => $prize_name[$lottery_rs['pid']]['i'],
		'softname' => $lottery_rs['softname'],
		'gift_num' => $lottery_rs['gift_number'],
	);
	exit(json_encode($array));
}else{
	$tplObj -> out['static_url'] = $configs['static_url'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['tpl'] = 'lottery';	
	$tplObj -> out['img_url'] = getImageHost();	
	list($ranking_config,$activity_arr) = get_config($active_id);	
    $ranking_config['rank_lottery_desc_text'] = str_replace(array("\r\n", "\r", "\n"), "", htmlspecialchars_decode($ranking_config['rank_lottery_desc_text']));
	$tplObj -> out['ranking_config'] = $ranking_config;	
	list($money,$lottery_num) = user_lottery_num($uid,$active_id);	
	// if($active_id == 370){
		// $lottery_num = 2;
	// }
	$tplObj -> out['money'] = $money ? $money : 0;
	$tplObj -> out['lottery_num'] = $lottery_num;
	$tplObj -> out['top10_prize'] = get_top10_prize($active_id);
	list($list,$prize_level) = get_prize_name($active_id);
	$tplObj -> out['prize_results'] = $prize_level;
	$tplObj -> out['award_list'] = get_award_all($active_id);
	//用户礼包兑换信息
	$gift_prize_list = get_user_kind_gift($uid,$active_id);
	//用户实物兑换信息
	$kind_award_list = get_user_kind_award($uid,$active_id);
	if(!$gift_prize_list && !$kind_award_list){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;//有中奖信息	
	}		
	$tplObj -> display('lottery/ranking/lottery.html');
}

