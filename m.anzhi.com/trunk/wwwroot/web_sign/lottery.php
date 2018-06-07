<?php
include_once ('./fun.php');
	
if($_POST){
	if($_POST['shorturl'] == 1){
		//分享短链
		load_helper('utiltool');
		$prizename = $_POST['prizename'];
		$shorturl =  $activity_host."/lottery/red_ffl/share.php?pageid=8&prizename=".$prizename."&pid=".$_SESSION['USER_ID']."&username=".$_SESSION['USER_NAME'];
		$short_url = shortenSinaUrl($shorturl);			
		$array = array(
			'code' => 1,
			'short_url' => $short_url,
		);
		exit(json_encode($array));		
	}
	$did = $_POST['did'];
	$cid = $_POST['cid'];
	if(!$did && !$cid){
		exit(json_encode(array('code'=>0,'msg'=>'DID和CID不存在')));
	}	
	$sign_config = sign_config($uid);
	//抽奖日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"mac" => $_SESSION['MAC'],
		"mid" => $m_arr['id'],
		'did' => $did ? $did : 0,
		'cid' => $_POST['cid'] ? $_POST['cid'] : 0,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => '',//pid
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"type_lottery" =>3,
		"type_name" =>'九宫格',
		"award_name" =>'',
		'key' => 'lottery'
	);
	//当天是否抽奖和签到
	if($_POST['did']){
		$did = $sign_config[$today]['did'];
		if($sign_config[$today]['status'] == 0){	
			$log_data['msg'] = '请先签到再抽奖';
			permanentlog($log_key, json_encode($log_data));
			exit(json_encode(array('code'=>0,'msg'=>'请先签到再抽奖')));
		}	
		$is_lottery_key = "{$prefix}:{$m_arr['id']}:is_lottery:".$uid.":".$today;
		if($redis->get($is_lottery_key) == 1){		
			$log_data['msg'] = '当天已抽奖';
			permanentlog($log_key, json_encode($log_data));		
			exit(json_encode(array('code'=>0,'msg'=>'当天已抽奖')));
		}
		$ret = $redis->setnx($is_lottery_key,1,86400);
		if(!$ret){	
			$log_data['msg'] = '当天已抽奖';
			permanentlog($log_key, json_encode($log_data));			
			exit(json_encode(array('code'=>0,'msg'=>'当天已抽奖')));
		}	
		//获取谢谢参与等级
		$nowin_level = get_nowin_level($did);			
	}else{
		$day =  $sign_config[$today]['level'];	
		$continuity = get_sign_days($uid,1,$sign_config,$day);
		$continuity_config_key = "{$prefix}:{$m_arr['id']}:continuity_config:".$uid;
		$continuity_config = $redis->get($continuity_config_key);	
		if($continuity_config[$_POST['cid']]['is_lottery'] == 1){
			$log_data['msg'] = '已抽奖';
			permanentlog($log_key, json_encode($log_data));				
			//exit(json_encode(array('code'=>0,'msg'=>'已抽奖')));
		}
		if($continuity < $continuity_config[$_POST['cid']]['count']){
			$log_data['msg'] = '连续签到天数不足';
			permanentlog($log_key, json_encode($log_data));				
			exit(json_encode(array('code'=>0,'msg'=>'连续签到天数不足')));	
		}	
		$continuity_config[$_POST['cid']]['is_lottery'] = 1;
		$redis->set($continuity_config_key,$continuity_config,86400*60);
		//参与抽奖人数
		$lottery_num_key = "{$prefix}:{$m_arr['id']}:lottery_num:".$_POST['cid'];
		$lottery_num = $redis->get($lottery_num_key);
		if($lottery_num){
			$redis -> setx('incr',$lottery_num_key, 1);
		}else{
			$redis -> set($lottery_num_key, 1,86400*60);
		}	
		//获取谢谢参与等级
		$nowin_level = get_nowin_level(0,$cid);			
	}	
		
	//刷出来的次数 抽奖直接不中
	// $device_num = device_user_num($uid);
	// if($device_num >= 5){
		// $log_data['msg'] = '[刷]同设备登陆3个账号以上';
		// permanentlog($log_key, json_encode($log_data));			
		// //用户已用抽奖次数+1
		// save_deduction_num($uid,"deduction_num");	
		// $array = array(
			// 'code' => 1,
			// 'type'=>-1,
			// 'prize_rank' => $nowin_level,
			// 'msg'=>'哎呀，什么都没有抽中啊<br/>下次继续加油哦！'
		// );
		// exit(json_encode($array));			
	// }
	$sign_key_today = $prefix.":".$m_arr['id'].':sign:'.$_SESSION['USER_UID'].':'.$today;
	$home_key_today = $prefix.":".$m_arr['id'].':home:'.$uid.':'.$today;
	$sign_key_today_time = $redis->get($sign_key_today);
	$home_key_today_time = $redis->get($home_key_today);
	// if($configs['is_test'] != 1 && ($sign_key_today_time==$home_key_today_time||$home_key_today_time==$time)){ //刷的直接不中奖
		// $log_data['msg'] = '[刷]同一秒操作';
		// permanentlog($log_key, json_encode($log_data));		
		// //用户已用抽奖次数+1
		// save_deduction_num($uid,"deduction_num");	
		// $array = array(
			// 'code' => 1,
			// 'type'=>-1,
			// 'prize_rank' => $nowin_level,
			// 'msg'=>'哎呀，什么都没有抽中啊<br/>下次继续加油哦！'
		// );		
		// exit(json_encode($array));	
	// }else{
		load_helper('task');
		$task_client = get_task_client();
		$new_array = array(
			'prefix' => $prefix,
			'uid' => $uid,
			'mid' => $m_arr['id'],
			'did' => $did ? $did : 0,
			'cid' => $_POST['cid'] ? $_POST['cid'] : 0,
			'username' => $_SESSION['USER_NAME'],
			'activityName' => 'WEB每日签到',
			'imei' => $_SESSION['DEVICEID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
		);
		$the_award = $task_client->do('web_sign_lottery', json_encode($new_array));
		$lottery_rs = json_decode($the_award,true);		
		//用户已用抽奖次数+1
		save_deduction_num($uid,"deduction_num");	
		if($the_award == -1){//未中奖
			$log_data['msg'] = '未中奖';
			permanentlog($log_key, json_encode($log_data));			
			$array = array(
				'code' => 1,
				'type'=>-1,
				'prize_rank' => $nowin_level,
				'msg'=>'哎呀，什么都没有抽中啊<br/>下次继续加油哦！'
			);		
			exit(json_encode($array));	
		}			
		if($lottery_rs['code'] == 0){
			exit($the_award);
		}
	//}
	permanentlog($log_key, json_encode($log_data));		
	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"mac" => $_SESSION['MAC'],
			'mid' => $m_arr['id'],
			'did' => $did ? $did : 0,
			'cid' => $_POST['cid'] ? $_POST['cid'] : 0,			
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['prize_rank'],//pid
			"user" => $_SESSION['USER_NAME'],
			"package" =>  $lottery_rs['type'] == 5  ? $lottery_rs['package'] : '',
			"softname" => $lottery_rs['type'] == 5 ? $lottery_rs['softname'] : '',
			"gift" => $lottery_rs['type'] == 4 || $lottery_rs['type'] == 5 ? $lottery_rs['gift_number'] : '',
			'uid'=>$uid,
			'type'=>$lottery_rs['type'],//奖品类别1:实物奖,2:充值卡,3:礼券,4:礼包5:礼包（直接发放）		
			"type_lottery" =>3,
			"type_name" =>'九宫格',			
			"award_name" => $lottery_rs['prizename'],
			'key' => 'award'
	);
	permanentlog($log_key, json_encode($log_data));	
	$my_prize_key = "{$prefix}:{$m_arr['id']}:my_prize:is_expired:0:".$uid;
	$redis->delete($my_prize_key);	
	$array = array(
		'code' => 1,
		'prizename' => $lottery_rs['prizename'],
		'prize_rank' => $lottery_rs['prize_rank'],
		'softname' => $lottery_rs['softname'],
		'gift_number' => $lottery_rs['gift_number'],
		'package' => $lottery_rs['package'],
		'type'=>$lottery_rs['type'],
		'award_id'=>$lottery_rs['award_id'],
	);
	if(in_array($lottery_rs['type'],array(1,2))){
		$array['expiration'] = date("Y-m-d",$time+30*86400);
	}else{
		$option = array(
			'where' => array(
				'id'=>$lottery_rs['award_id']
			),
			'table' => 'qd_draw_award',
			'field' => 'virtual_end_tm',
		);
		$prize_info = $model->findOne($option,'lottery/lottery');
		$array['expiration'] = date("Y-m-d",$prize_info['virtual_end_tm']);
	}
	exit(json_encode($array));
}else{
	$did = $_GET['did'];
	$cid = $_GET['cid'];
	if(!$did && !$cid){
		exit;
	}
	if($did > 0){
		$is_lottery_key = "{$prefix}:{$m_arr['id']}:is_lottery:".$uid.":".$today;
		if($redis->get($is_lottery_key) == 1){	
			$tplObj -> out['lottery_num'] = 0;
		}else{
			$tplObj -> out['lottery_num'] = 1;
		}		
	}else{
		$continuity_config_key = "{$prefix}:{$m_arr['id']}:continuity_config:".$uid;
		$continuity_config = $redis->get($continuity_config_key);	
		if($continuity_config[$cid]['is_lottery'] == 1){		
			$tplObj -> out['lottery_num'] = 0;
		}else{
			$tplObj -> out['lottery_num'] = 1;
		}	
	}
	$tplObj -> out['prize_results'] = get_now_prize($did,$cid);
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['did'] = $did;
	$tplObj -> out['cid'] = $cid;
	$tplObj -> out['pid'] = $_SESSION['USER_ID'];
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['mid'] = $m_arr['id'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tplObj -> out['img_url']  = getImageHost();
	$tpl = "{$prefix}/lottery.html";
	$tplObj -> display($tpl);
}

