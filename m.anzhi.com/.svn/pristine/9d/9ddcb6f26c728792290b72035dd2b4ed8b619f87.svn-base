<?php
include_once ('./fun.php');
$card_config = signature_card_config();
$user_card_config = get_user_card_config($uid);
$jb_num = $user_card_config['jb_num'];
$buy_num = intval($card_config[0]['buy_num']);
if($_POST){
	if($_POST['is_consum'] == 1){
		$money = intval($card_config[0]['price']);
		//金币消费日志
		$log_data = array(
			"imsi"			=>	$_SESSION['USER_IMSI'],
			"device_id"		=>	$_SESSION['DEVICEID'],
			"mac" 			=>  $_SESSION['MAC'],
			"mid"			=>	$m_arr['id'],
			"ip"			=>	$_SERVER['REMOTE_ADDR'],
			"sid"			=>	$sid,
			"time"			=>	$time,
			"user"			=>	$_SESSION['USER_NAME'],
			'money'			 => $money,
			'uid'			=>	$uid,
			'key'			=>	'jb_consume',
		);
		if($jb_num >= $buy_num ){
			$log_data['msg'] = '当月已无可购补签卡';
			permanentlog($log_key, json_encode($log_data));	
			exit(json_encode(array('code'=>0,'msg'=>'当月已无可购补签卡')));
		}
		$lock_key = $prefix.":".$m_arr['id'].':lock:'.$uid;
		$ret = $redis->setnx($lock_key,1,2);
		if(!$ret){		
			exit(json_encode(array('code'=>0,'msg'=>'操作太频繁请稍后再试~~')));
		}	
		$user_jb_num = check_user_jb_num($uid);
		if($user_jb_num>0 && $user_jb_num > $buy_num ){
			$log_data['msg'] = '当月已无可购补签卡'.$user_jb_num ."==".$buy_num;
			permanentlog($log_key, json_encode($log_data));			
			exit(json_encode(array('code'=>0,'msg'=>'当月已无可购补签卡')));
		}		
		//金币购买补签卡
		$res = jb_consume($uid,$money);
		if($res['code'] == 0){
			$log_data['msg'] = $res['msg'];
			permanentlog($log_key, json_encode($log_data));			
			exit(json_encode(array('code'=>0,'msg'=>$res['msg'])));
		}
		permanentlog($log_key, json_encode($log_data));	
		exit(json_encode(array('code'=>1,'water_id'=>$res['water_id'])));
	}else if($_POST['is_consum_res'] == 1){
		//轮循结果
		$water_id = $_POST['water_id'];
		list($res_status,$msg) = jb_consume_res($water_id,$uid);
		exit(json_encode(array('code'=>$res_status,'msg'=>$msg)));
	}else if($_POST['is_task'] == 1){
		$log_data = array(
			'time'	=>	$time,
			'imsi'	=>	$_SESSION['USER_IMSI'],
			'uid'	=>	$uid,
			'sid' => $sid,	
			'username'	=>	$_SESSION['USER_NAME'],
			'device_id'	=>	$_SESSION['DEVICEID'],
			"mac" => $_SESSION['MAC'],
			"mid"	=>	$m_arr['id'],
			'type' => 1,//0：金币购买补签卡 1：做任务获得补签卡
			'key'	=>	'signature_card'
		);			
		if($user_card_config['task_num'] >= intval($card_config[1]['task_num'])){
			$log_data['msg'] = '当月无已无补签卡任务';
			permanentlog($log_key, json_encode($log_data));		
			exit(json_encode(array('code'=>0,'msg'=>'当月无已无补签卡任务')));
		}
		$user_card_config_key = $prefix.":".$m_arr['id'].':user_card_config:'.$uid;
		$redis->setx("HINCRBY",$user_card_config_key,"task_num",1);	
		$sign_config = sign_config($uid);
		if($sign_config[$today]['is_card_task'] == 1){
			$log_data['msg'] = '当天已领取';
			permanentlog($log_key, json_encode($log_data));				
			exit(json_encode(array('code'=>0,'msg'=>'当天已领取')));
		}
		$sign_config[$today]['is_card_task'] = 1;		
		$sign_redis->set("{$prefix}:{$m_arr['id']}:tm_config:".$uid, $sign_config, 86400*60);
		//补签卡总数
		save_deduction_num($uid,"cards_num_task");
		//已领
		$task_status_key = $prefix.":".$m_arr['id'].':task_status:'.$uid;
		$redis->set($task_status_key,2,30*86400);		
		permanentlog($log_key, json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'领取成功')));		
	}else if($_POST['downapp'] == 1){
		if($_POST['from'] == 1){
			//下载
			$pkg = $_POST['package'];
			if($user_card_config['task_num'] < intval($card_config[1]['task_num'])){
				user_soft_status($uid,$pkg,1);
			}
			$real_package = $redis->get("real_package:post_old_package_{$pkg}:");
			if(!$real_package){
				$real_package = get_real_package($package);
			}
		}		
		$log_data = array(
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			"mac" => $_SESSION['MAC'],
			"mid"	=>	$m_arr['id'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'package' => $pkg,
			'real_package' => $real_package,
			'key' => 'download_soft'
		);		
		permanentlog($log_key, json_encode($log_data));
		exit;
	}else if($_POST['installApp'] == 1){
		//安装
		$pkg = $_POST['package'];
		$log_data = array(
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			"mac" => $_SESSION['MAC'],
			"mid"	=>	$m_arr['id'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'package' => $pkg,
			'sid' => $sid,
			'time' => $time,
			'key' => 'install_soft'
		);
		permanentlog($log_key, json_encode($log_data));
		exit;		
	}else if($_POST['openapp'] == 1){
		if($_POST['from'] == 1){
			//打开
			$pkg = $_POST['package'];
			if($user_card_config['task_num'] < intval($card_config[1]['task_num'])){
				user_soft_status($uid,$pkg,3);
			}
		}
		$log_data = array(
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			"mac" => $_SESSION['MAC'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			"mid"	=>	$m_arr['id'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'open_soft'
		);
		permanentlog($log_key, json_encode($log_data));
		exit;			
	}else if($_POST['is_share'] == 1){
		//分享
		$log_data = array(
			'uid'=>$uid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'username' => $_SESSION['USER_NAME'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			"mac" => $_SESSION['MAC'],
			'time' => $time,
			"mid"	=>	$m_arr['id'],
			'sid' => $sid,
			'key' => 'share'
		);		
		permanentlog($log_key, json_encode($log_data));
		exit;			
	}
}else{
	if($_GET['is_rule'] == 1){
		$tpl = "{$prefix}/signature_rule.html";
		$tplObj -> out['prefix'] = $prefix;	
		$tplObj -> out['static_url'] = $configs['static_url'];
		$tplObj -> display($tpl);		
		exit;
	}
	$num = check_card_num($uid,$jb_num,$buy_num);
	if($num && $num != $jb_num){
		$user_card_config['jb_num'] = $num;
	}
	//0不可领1可领2已领
	$task_status_key = $prefix.":".$m_arr['id'].':task_status:'.$uid;
	$tplObj -> out['task_status'] = $redis->get($task_status_key);
	//当天是否领取
	$sign_config = sign_config($uid);
	$tplObj -> out['is_card_task'] = $sign_config[$today]['is_card_task'];	
	$tplObj -> out['card_config'] = $card_config;
	$tplObj -> out['user_card_config'] = $user_card_config;
	$tpl = "{$prefix}/signature_card.html";
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['mid'] = $m_arr['id'];	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['is_test'] = $configs['is_test'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	//任务软件状态
	$soft_status_key = $prefix.":".$m_arr['id'].':soft_status:'.$uid;
	$soft_status = $redis->get($soft_status_key);	
	$tplObj -> out['json_soft_status'] = json_encode($soft_status);
	$tplObj -> display($tpl);		
}