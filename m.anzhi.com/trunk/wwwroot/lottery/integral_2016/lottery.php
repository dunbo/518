<?php
include_once ('./fun.php');
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/{$prefix}/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}
//1兑换 2抽奖
if($_POST['type_lottery'] ==1){
	$prize_info = get_prize($_POST['position']);
	//抢夺日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => $prize_info['position'],//pid
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"award_name" => $prize_info['prizename'],
		"start_time" => $prize_info['start_time'],//抢夺开始时间
		'num' => $_POST['num'],		
		"type_lottery" => 4,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换
		'key' => 'lottery'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	if($_POST){
		$rest_integral = get_rest_integral($uid);

		if($rest_integral <= 0){
			exit(json_encode(array('code'=>0,'msg'=>'您当前积分不足，请您充值后再抢夺')));
		} 		
		if($prize_info['start_time'] > $time){
			exit(json_encode(array('code'=>0,'msg'=>$prize_info['prizename'].'抢夺未开始')));
		} 		
		if($prize_info['res_num'] <= 0){
			exit(json_encode(array('code'=>0,'msg'=>'该奖品已经售馨')));
		} 	
		if($_POST['num']*$prize_info['prize_integral'] > $rest_integral){
			exit(json_encode(array('code'=>0,'msg'=>'您当前积分不足，请您充值后再抢夺')));
		}	
		//var_dump($rest_integral);exit;
		load_helper('task');
		$task_client = get_task_client();
		$new_array = array(
			'uid' => $uid,
			'aid' => $active_id,
			'username' => $_SESSION['USER_NAME'],
			'start_time' => $prize_info['start_time'],
			'pid' => $prize_info['id'],
			'pic_name' => $prize_info['pic_name'],
			'prize_integral' => $prize_info['prize_integral'],
			'prizename' => $prize_info['prizename'],
			'num' => $_POST['num'],
			'lottery_chances' => $_POST['lottery_chances'],
			'position' => $_POST['position'],
		);
		$the_award = $task_client->do("{$prefix}_worker", json_encode($new_array));
		$lottery_rs = json_decode($the_award,true);	
		if($lottery_rs['code'] == 0 ){
			exit($the_award);
		}
		//加抽次数
		//add_lottery_num($uid);
		//兑换成功日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['position'],
			"user" => $_SESSION['USER_NAME'],
			'uid'=>$uid,
			//'num' => $_POST['num'],			
			'prize_integral' => $prize_info['prize_integral'],			
			"award_name" => $prize_info['prizename'],
			"type_lottery" => 4,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换
			'key' => 'award'
		);
		$gift_str = '';
		for($i=1; $i <= $_POST['num']; $i++ ){
			if($_POST['position'] == 4){
				$new_array=array(
					'uid'=>$uid,
					'aid'=>$active_id,
					'username'=>$_SESSION['USER_NAME'],
					'prefix'=>$prefix,
				);
				$the_award = $task_client->do('custom_lottery', json_encode($new_array));	
				$lottery_rt = json_decode($the_award,true);
				$gift_info = json_decode($lottery_rt['gift_number'],true);
				$log_data['gift'] =  $gift_info['gift_number'];
				$gift_str .= $gift_info['gift_number'].";<br/>";
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
				$redis -> lPush($key,json_encode($arr),30*60);					
			}
			permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		}
		exit(json_encode(array('code'=>1,'msg'=> $prize_info['prizename'],'num'=>$lottery_rs['num'],'gift'=>$gift_str)));
	}
}else{
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
		"type_lottery" => 2,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换
		'key' => 'lottery',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	//剩余抽奖次数
	$lottery_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$now_num = $redis->setx('incr',$lottery_key,-1);
	if($now_num < 0){
		$redis->set($lottery_key,0,30*60);
		exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
	}	
	load_helper('task');
	$task_client = get_task_client();
	$new_array=array(
		'uid'=>$uid,
		'aid'=>$active_id,
		'username'=>$_SESSION['USER_NAME'],
		'prefix'=>$prefix,
	);
	$new_array['package'] = get_gift_pkg($active_id,$uid,$_POST['pkg'],$prefix);	
	$the_award = $task_client->do('custom_lottery', json_encode($new_array));	
	$lottery_rs = json_decode($the_award,true);
	if($lottery_rs['prize_rank'] == 6){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
		del_gift_pkg($active_id,$uid,$gift_info['package'],$prefix);
	}	
	//用户已用抽奖次数+1
	save_deduction_num_new($uid,$active_id,$_SESSION['USER_NAME'],$prefix,"one_dollar_userinfo");
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
		"award_level" => $lottery_rs['prize_rank']  ,
		"user" => $_SESSION['USER_NAME'],
		"package" =>  $lottery_rs['prize_rank'] ==6 ? $gift_info['package'] : '',
		"softname" => $lottery_rs['prize_rank'] ==6 ? $gift_info['softname'] : '',
		"gift" =>  $lottery_rs['prize_rank'] ==6 ? $gift_info['gift_number'] : '',
		'uid'=>$uid,
		"award_name" => $lottery_rs['prize_rank'] ==6 ? "礼包" : $lottery_rs['prizename'],
		"type_lottery" => 2,//1.老虎机，2.大转盘，3.九宫格，4.积分兑换
		'key' => 'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	if($lottery_rs['prize_rank'] == 6){
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
		$redis -> lPush($key,json_encode($arr),30*60);	
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['prize_rank'],
			'prizename' => $lottery_rs['prizename'],
			'time' => date("Y-m-d",$time)
		);		
		$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
		$redis -> lPush($key,json_encode($arr),30*60);
	}	
	$array = array(
		'code' => 1,
		'pid' => $lottery_rs['prize_rank'] == 6 ? 0 : $lottery_rs['prize_rank'],
		'package' => $lottery_rs['prize_rank'] ==6 ? $gift_info['package'] :'',
		'softname' => $lottery_rs['prize_rank'] ==6 ? $gift_info['softname'] : '',
		'gift_num' => $lottery_rs['prize_rank'] ==6 ? $gift_info['gift_number'] : '',
		'prizename' => $lottery_rs['prizename'],
	);
	exit(json_encode($array));	
}
