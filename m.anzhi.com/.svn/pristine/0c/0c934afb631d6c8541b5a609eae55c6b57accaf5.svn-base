<?php
	include_once ('./eternal_fun.php');
	session_begin();
	if(isset($_SESSION['USER_UID'])){//已登录
		$uid = $_SESSION['USER_UID'];
	}else{//未登录 跳转到首页
		$url = "http://promotion.anzhi.com/lottery/young_west_reserve/{$prefix}_index.php";
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}
	if($_GET['appointment'] == 1){
		//用户预约日志
		$log_data = array(
			'uid'=>$uid,
			'username' => $_SESSION['USER_NAME'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'time' => $time,
			'activity_id' => $active_id,
			'key' => 'reserve'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
		add_lottery_num($uid);
		$appointment_key = "{$prefix}:".$active_id.":is_sign:".$uid;
		$redis->set($appointment_key,1,86400*10);
		$lottery_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
		$now_num = $redis->set($lottery_key,1,60*30);	
		exit(json_encode(array('code'=>1)));			
	}
	if($_POST){
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
			'key' => 'lottery',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		//剩余抽奖次数
		$lottery_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
		$now_num = $redis->setx('incr',$lottery_key,-1);
		if($now_num < 0){
			$redis->set($lottery_key,0,30*60);
			exit(json_encode(array('code'=>0,'msg'=>'抽奖次数不足')));
		}			
		load_helper('task');
		$task_client = get_task_client();
		$new_array=array(
			'uid'=>$uid,
			'aid'=>$active_id,
			'username'=>$_SESSION['USER_NAME'],
			'prefix'=>$prefix,
		);		
		$the_award = $task_client->do('custom_lottery', json_encode($new_array));
		$lottery_rs = json_decode($the_award,true);		
		if($lottery_rs['prize_rank'] == 3){
			$gift_info = json_decode($lottery_rs['gift_number'],true);
		}	
		//用户已用抽奖次数+1
		save_deduction_num_new($uid,$active_id,$_SESSION['USER_NAME'],$prefix,"valentine_draw_userinfo");		
		if($the_award == -1){
			exit(json_encode(array('code'=>1,'pid'=>-1,'msg'=>'未中奖')));
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
			"package" =>  $lottery_rs['prize_rank'] ==3 ? $gift_info['package'] : '',
			"softname" => $lottery_rs['prize_rank'] ==3 ? $gift_info['softname'] : '',
			"gift" =>  $lottery_rs['prize_rank'] ==3 ? $gift_info['gift_number'] : '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['prize_rank'] ==3 ? "礼包" : $lottery_rs['prizename'],
			'key' => 'award',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
		if($lottery_rs['prize_rank'] == 3){
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
			//礼包的所有兑换信息
			$key = "{$prefix}:{$active_id}_gift_prize:{$uid}";
			$redis -> lPush($key,json_encode($arr),30*60);	
		}else{
			//实物的所有兑换信息
			$arr = array(	
				'uid' => $uid,
				'pid' =>  $lottery_rs['pid'],
				'prizename' => $lottery_rs['prizename'],
				'time' => date("Y-m-d",$time)
			);		
			$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
			$redis -> lPush($key,json_encode($arr),30*60);
		}	
		$array = array(
			'code' => 1,
			'pid' => $lottery_rs['prize_rank'],
			'package' => $lottery_rs['prize_rank'] ==3 ? $gift_info['package'] :'',
			'softname' => $lottery_rs['prize_rank'] ==3 ? $gift_info['softname'] : '',
			'gift_num' => $lottery_rs['prize_rank'] ==3 ? $gift_info['gift_number'] : '',
			'prizename' => $lottery_rs['prizename'],
		);
		exit(json_encode($array));			
	}