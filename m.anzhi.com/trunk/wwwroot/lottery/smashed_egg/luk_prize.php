<?php
include_once ('./fun.php');
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = $activity_host."/lottery/{$prefix}/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}
if($_POST){
	$position = $_POST['position'];
	if($position == 11){
		$luk_num = 100;
	}else if($position == 12){
		$luk_num = 200;
	}else if($position == 13){
		$luk_num = 300;
	}else if($position == 14){
		$luk_num = 500;
	}else if($position == 15){
		$luk_num = 1000;
	}else if($position == 16){
		$luk_num = 2000;
	}	
	//领取日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => $position,
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		'prize_integral' => $luk_num,		
		"type_lottery" => 4,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换		
		'key' => 'lottery',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	$luk_key = "{$prefix}:".$active_id.":luk:".$uid;	
	$now_num = $redis->get($luk_key);
	if($now_num <= 0 || ($now_num < $luk_num)){
		exit(json_encode(array('code'=>0,'msg'=>'可用幸运值不足')));
	}	
	$now_luk_num = $redis->setx('incr',$luk_key,-$luk_num);	
	if($now_luk_num < 0){
		$redis->set($luk_key,intval($now_num),15*60);	
		exit(json_encode(array('code'=>0,'msg'=>'可用幸运值不足')));
	}
	load_helper('task');
	$task_client = get_task_client();
	$new_array=array(
		'uid'=>$uid,
		'aid'=>$active_id,
		'username'=>$_SESSION['USER_NAME'],
		'prefix'=>$prefix,
		'position' => $position,
	);		
	$the_award = $task_client->do('smashed_egg', json_encode($new_array));	
	$lottery_rs = json_decode($the_award,true);
	if($lottery_rs['code'] == 1){
		if($lottery_rs['msg'] == '失败'){
			$redis->set($luk_key,intval($now_num),15*60);	
			exit($the_award);
		}
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['pid'],
			'prizename' => $lottery_rs['prizename'],
			'time' => date("Y-m-d",$time)
		);		
		$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
		$redis -> lPush($key,json_encode($arr),30*60);
		save_luk($uid,$position);
		//领取成功日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $_POST['position'],
			"user" => $_SESSION['USER_NAME'],
			'uid'=>$uid,
			'prize_integral' => $luk_num,
			"award_name" =>  $lottery_rs['prizename'],
			"type_lottery" => 4,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换		
			'key' => 'award',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
	}
	exit($the_award);
}else{
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	//幸运值
	$tplObj -> out['luk_num'] = get_luk($uid);	
	
	$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');
	$pid_arr = get_lottrey_pid();	
	foreach($kind_award_list as $k => $v){
		if($pid_arr[$v['pid']] <=8){
			unset($kind_award_list[$k]);
		}
	}	
	if($kind_award_list){
		$tplObj -> out['is_user_winning'] = 1;		//有奖
	}else{
		$tplObj -> out['is_user_winning'] = 2;
	}
	if( isset($_GET['new']) ) {
		$tpl = "lottery/{$prefix}/luk_prize_new.html";
	}else{
		$tpl = "lottery/{$prefix}/luk_prize.html";
	}
	
	$tplObj -> display($tpl);	
}