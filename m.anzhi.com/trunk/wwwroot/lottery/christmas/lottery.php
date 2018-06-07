<?php
include_once ('./fun.php');
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}	
$build_query = http_build_query($_GET);
session_begin($sid);
$time = time();
$now_day = date('Ymd');
if($_POST){
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		$uid = $_SESSION['USER_UID'];
	}else{//未登录 跳转到首页
		$url = "http://promotion.anzhi.com/lottery/christmas/lottery.php?".$build_query;
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}	
	$is_luxury = $_POST['is_luxury'];
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
		"is_luxury" => $is_luxury,//1豪华2普通3积分兑换
		'key' => 'lottery'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	//剩余抽奖次数
	$now_num = $redis->setx('incr',"christmas:{$active_id}_lottery_num:".$uid.":".$now_day,-1);
	if($now_num < 0){
		$redis->set("christmas:{$active_id}_lottery_num:".$uid.":".$now_day,0);
		exit(json_encode(array('code'=>0,'msg'=>'抱歉，您账号今日可用抽奖次数已用完！','title'=>"【抽奖次数已用完】")));
	}	
	//剩余积分不足
	if($is_luxury == 1){
		$num = 30;
	}else{
		$num = 10;
	}
	$now_integral_num = $redis->get("christmas:{$active_id}_rest_integral:".$uid);
	if($now_integral_num < $num){
		$redis->setx('incr',"christmas:{$active_id}_lottery_num:".$uid.":".$now_day);
		exit(json_encode(array('code'=>0,'msg'=>'您账号当前可用积分不足，请尽快充值获取积分！','title'=>"【积分不足，不能抽奖】")));
	}	
	$integral_num = $redis->setx('incr',"christmas:{$active_id}_rest_integral:".$uid,-intval($num));
	if($integral_num < 0){
		$redis->setx('incr',"christmas:{$active_id}_lottery_num:".$uid.":".$now_day);
		$redis->set("christmas:{$active_id}_rest_integral:".$uid,0);
		exit(json_encode(array('code'=>0,'msg'=>'您账号当前可用积分不足，请尽快充值获取积分！','title'=>"【积分不足，不能抽奖】")));
	}		
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'is_luxury' => $is_luxury,
	);	
	if($is_luxury == 2){
		$new_array['package'] = get_gift_pkg($active_id,$uid,$_POST['pkg'],"christmas");
	}
	$the_award = $task_client->do('christmas_lottery', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);		
	if($lottery_rs['pid'] == 0 && $is_luxury == 2){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
		del_gift_pkg($active_id,$uid,$gift_info['package'],"christmas");
	}	
	//用户已用积分
	save_deduction_integral($uid,$active_id,$_SESSION['USER_NAME'],$num);
	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $lottery_rs['pid'] == 0 ? 0 : $lottery_rs['prize_rank']  ,
			"user" => $_SESSION['USER_NAME'],
			"package" => $lottery_rs['pid'] ==0 ? $gift_info['package'] : '',
			"softname" => $lottery_rs['pid'] ==0 ? $gift_info['softname'] : '',
			"gift" =>  $lottery_rs['pid'] ==0 ? $gift_info['gift_number'] : '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['pid'] ==0 ? "礼包" : $lottery_rs['prizename'],
			"is_luxury" => $is_luxury,//1豪华2普通3积分兑换
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
			'time' => date("Y-m-d",$time) ,
		);
		//礼包的所有兑换信息
		$key = "christmas:{$active_id}_gift_prize:{$uid}";
		$redis -> lPush($key,json_encode($arr));	
		$redis -> expire($key,86400*10);
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['prize_rank'],
			'prizename' => $lottery_rs['prizename'],
			'time' => date("Y-m-d",$time)
		);			
		$arr['username'] = str_replace_cn_new($_SESSION['USER_NAME'], 1, -2 );
		$key = "christmas:{$active_id}_lottery_record:".$uid;
		$redis -> lPush($key,json_encode($arr));	
		$redis -> expire($key,86400*10);
	}
	$array = array(
		'code' => 1,
		'pid' => $lottery_rs['pid'] == 0 ? 0 : $lottery_rs['prize_rank']  ,
		'package' => $gift_info['package'],
		'softname' => $gift_info['softname'],
		'gift_num' => $gift_info['gift_number'],
		'prizename' => $lottery_rs['prizename'],
	);
	exit(json_encode($array));
}else{
	$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
	$login_url = $center_url."http://promotion.anzhi.com/lottery/christmas/lottery.php?".$build_query;
	$tplObj -> out['login_url'] = $login_url;	
	user_loging_new();	
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		//登录日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'login'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		$uid = $_SESSION['USER_UID'];
		$tplObj -> out['username'] = $_SESSION['USER_NAME'];
		$tplObj -> out['is_login'] = 1;
		$tplObj -> out['uid'] = $uid;
		$kind_award_list = get_user_lottery_record($uid,$active_id);
		$integral_kind_list = get_user_integral_kind_record($uid,$active_id);
		$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"christmas","integral_kind_gift");	
		if(!$kind_award_list && !$integral_kind_list && !$kind_award_gift){
			$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
		}else{
			$tplObj -> out['is_user_winning'] = 1;
		}
		$lottery_num = $redis->get("christmas:{$active_id}_lottery_num:".$uid.":".$now_day);
		if($lottery_num === null){
			$lottery_num = 3;
			$redis -> set("christmas:{$active_id}_lottery_num:".$uid.":".$now_day,3,86400*2);
		}
		$tplObj -> out['lottery_num'] = $lottery_num;
		//用户积分、金额
		list($integral_num,$money) = get_user_integral($uid,$active_id);
		$tplObj -> out['integral_num'] = $integral_num ? $integral_num : 0;
		$tplObj -> out['money'] = $money ? $money : 0;
	}else{//未登录
		$tplObj -> out['is_login'] = 2;
		$tplObj -> out['lottery_num'] = 3;	
	}
	$tplObj -> out['top10_lottery'] = get_top10_lottery($active_id);
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display('lottery/christmas/lottery.html');	
}

