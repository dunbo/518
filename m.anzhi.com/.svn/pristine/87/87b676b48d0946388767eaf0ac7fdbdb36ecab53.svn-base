<?php
include_once ('./fun.php');
$is_lottery_key = "{$prefix}:{$m_arr['id']}:is_lottery:".$uid.":".$today;
$sign_config = sign_config($uid);
$same_day_config = $sign_config[$today];
if($_POST['open_red'] == 1){
	if($same_day_config['status'] == 0){	
		exit(json_encode(array('code'=>0,'msg'=>'请先签到')));
	}	
	if($redis->get($is_lottery_key) == 1){		
		exit(json_encode(array('code'=>0,'msg'=>'当天已拆过红包')));
	}
/*	
	//根据设备过滤红包（如A用户B用户在同一设备上预拆，B用户将无法拆红包）预拆红包
	if($sign_config[$today]['package']){
		$is_pre_red = isExistsBillByDevice($sign_config[$today]['redid'],$_SESSION['DEVICEID'],$_SESSION['MAC'],1);
		if(!$is_pre_red){
			$is_pre_red = isExistsBillByDevice($sign_config[$today]['redid'],$_SESSION['DEVICEID'],$_SESSION['MAC'],0);
		}
	}else{
		$is_pre_red = isExistsBillByDevice($sign_config[$today]['redid'],$_SESSION['DEVICEID'],$_SESSION['MAC'],0);
	}	
	if($is_pre_red){
		$is_lottery_key = "{$prefix}:{$m_arr['id']}:is_lottery:".$uid.":".$today;
		$redis->set($is_lottery_key,1,86400);		
		exit(json_encode(array('code'=>0,'msg'=>'同一设备、账号参与一次活动，不能重复领取红包')));
	}
*/	
	//拆红包日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"mac"	=>	$_SESSION['MAC'],
		"mid"	=>	$m_arr['id'],
		"red_id" => $same_day_config['redid'],
		"did" => $same_day_config['did'],
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		'key' => 'open_red'
	);	
	$where = array(
		'uid' => $uid,
		'mid' => $m_arr['id'],
		'did' => $same_day_config['did'],
		'red_id' => $same_day_config['redid'],
	);
	$option = array(
		'where' => $where,
		'table' => 'qd_draw_red',
		'field' => 'id,status,add_tm',	
	);
	$list = $model->findOne($option,'lottery/lottery');
	if($list){
		if($list['status'] == 2){
			$log_data['msg'] = '该红包已拆';
			permanentlog($log_key, json_encode($log_data));			
			$redis->set($is_lottery_key,1,86400);				
			exit(json_encode(array('code'=>0, 'msg'=>'该红包已拆')));
		}else{
			if($same_day_config['package'] && ($time-$list['add_tm']) >= 3600 ){
				$log_data['msg'] = '该红包已过期';
				permanentlog($log_key, json_encode($log_data));					
				$redis->set($is_lottery_key,1,86400);				
				exit(json_encode(array('code'=>0, 'msg'=>'该红包已过期')));						
			}			
			if($same_day_config['task_id'] > 0 && $same_day_config['package']){	
				$app_info = get_app_info(trim($same_day_config['package']));
				if($app_info == '""'){
					//软件被过滤，任务无法完成
					$list['status'] = 3;
					$log_data['msg'] = '软件被过滤，任务无法完成--app_info:'.$app_info."--package:".$same_day_config['package'];
				} 
			}else{
				$app_info = '';
			}
			$return_arr = array(
				'code'=>1,
				'id'=>$list['id'],
				'status'=> $list['status'],
				'app_info'=>$app_info,
				'lrts' => '密钥'
			);
			permanentlog($log_key, json_encode($log_data));
			exit(json_encode($return_arr));			
		}
	}else{
		$red_num = get_red_now($same_day_config['redid']);
		if($red_num < 1){
			$log_data['msg'] = '该红包已抢完';
			permanentlog($log_key, json_encode($log_data));					
			$redis->set($is_lottery_key,1,86400);				
			$return_arr = array(
				'code'=>1,
				'id'=>0,
				'status'=> 2,//已抢完
				'app_info'=>'',
				'lrts' => '密钥'
			);
			exit(json_encode($return_arr));				
		}		
		$data = array(
			'uid' => $uid,
			'mid' => $m_arr['id'],
			'did' => $same_day_config['did'],
			'red_id' => $same_day_config['redid'],
			'add_tm' => $time,
			'__user_table' => 'qd_draw_red'
		);	
		$ret =  $model->insert($data,'lottery/lottery');
		if($ret){
			if($same_day_config['task_id'] > 0 && $same_day_config['package']){	
				$app_info = get_app_info(trim($same_day_config['package']));
				if($app_info == '""'){
					//软件被过滤，任务无法完成
					$status = 3;
					$log_data['msg'] = '软件被过滤，任务无法完成--app_info:'.$app_info."--package:".$same_day_config['package'];
				} 				
			}else{
				$app_info = '';
			}
			$return_arr = array(
				'code'=>1,
				'id'=>$ret,
				'status'=> $status ? $status : 0,
				'app_info'=>$app_info,
				'lrts' => '密钥'
			);
			permanentlog($log_key, json_encode($log_data));				
			exit(json_encode($return_arr));
		}else{
			$log_data['msg'] = '红包流水入库失败';
			permanentlog($log_key, json_encode($log_data));	
			exit(json_encode(array('code'=>0, 'msg'=>'红包流水入库失败')));
		}
	}
}else if($_POST['open_red'] == 2){
	if($_POST['result'] == 2){
		$redis->set($is_lottery_key,1,86400);	
		exit(json_encode(array('code'=>1)));		
	}
	$red_num = get_red_now($same_day_config['redid']);
	if($red_num < 1){			
		$redis->set($is_lottery_key,1,86400);				
		exit(json_encode(array('code'=>1)));						
	}	
	$where = array(
		'uid' => $uid,
		'mid' => $m_arr['id'],
		'did' => $same_day_config['did'],
		'red_id' => $same_day_config['redid'],
	);
	$option = array(
		'where' => $where,
		'table' => 'qd_draw_red',
		'field' => 'id,status,money',	
	);
	$list = $model->findOne($option,'lottery/lottery');
	if($list['status'] == 2){
		//拆红包成功日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"mac"	=>	$_SESSION['MAC'],
			"mid"	=>	$m_arr['id'],
			"red_id" => $_POST['red_id'],
			"did" => $_POST['did'],		
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"user" => $_SESSION['USER_NAME'],
			'uid'=>$uid,
			'money' => $list['money'],
			'key' => 'open_red_success'
		);
		permanentlog($log_key, json_encode($log_data));
		$redis->set($is_lottery_key,1,86400);	
		exit(json_encode(array('code'=>1)));
	}else{
		exit(json_encode(array('code'=>0)));
	}
}
