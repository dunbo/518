<?php
include_once ('./fun.php');
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}	
$build_query = http_build_query($_GET);
session_begin($sid);
$time = time();
if($_POST){
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		$uid = $_SESSION['USER_UID'];
	}else{//未登录 跳转到首页
		$url = "http://promotion.anzhi.com/lottery/christmas/integral_award.php?".$build_query;
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}	
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
		"is_luxury" =>3,//1豪华2普通3积分兑换
		'key' => 'lottery'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$position = intval($_POST['position']);	
	if(!isset($position)){
		exit(json_encode(array('code'=>0,'msg'=>'抱歉，该奖品已被其他小伙伴先一步抢光！别气馁！已售罄的奖品，会每隔一段时间刷新新品！','title'=>"已售馨")));
	}
	$prize_info = get_prize_list($active_id,$position);
	$prize_integral = intval($prize_info['prize_integral']);
	//剩余积分不足
	$now_integral_num = $redis->get("christmas:{$active_id}_rest_integral:".$uid);
	if($now_integral_num < $prize_integral){
		exit(json_encode(array('code'=>0,'msg'=>'您账号当前可用积分不足，请尽快充值获取积分！','title'=>"积分不足，不能兑换")));
	}	
	if($prize_info['sold_out'] == 1 && $prize_info['start_time'] > $time){
		//兑换时间还没开始请耐心等待！
		exit(json_encode(array('code'=>0,'msg'=>'抱歉，该奖品已被其他小伙伴先一步抢光！别气馁！已售罄的奖品，会每隔一段时间刷新新品！','title'=>"已售馨")));
	}
	$pid = intval($prize_info['pid']);
	$ret = get_prize_num($active_id,$pid,$position);
	if(!$ret){
		exit(json_encode(array('code'=>0,'msg'=>'抱歉，该奖品已被其他小伙伴先一步抢光！别气馁！已售罄的奖品，会每隔一段时间刷新新品！','title'=>"已售馨")));
	}	
	//缓存锁
	$lock = $redis->get("christmas:{$active_id}_lock:".$position);
	if($lock){
		exit(json_encode(array('code'=>0,'msg'=>'抱歉，该奖品已被其他小伙伴先一步抢光！别气馁！已售罄的奖品，会每隔一段时间刷新新品！','title'=>"已售馨")));
	}
	$redis->set("christmas:{$active_id}_lock:".$position,1,600);	
	$integral_num = $redis->setx('incr',"christmas:{$active_id}_rest_integral:".$uid,-$prize_integral);
	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'position' => $position,
		'is_luxury' => 3,
	);	
	$the_award = $task_client->do('christmas_lottery', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);	
	if($lottery_rs['code'] == 0 ){
		exit($the_award);
	}	
	//用户已用积分
	save_deduction_integral($uid,$active_id,$_SESSION['USER_NAME'],$prize_integral);
	//刷奖品缓存
	brush_prize_cache($active_id,$position,$lottery_rs);	
	//抽奖成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" =>  $lottery_rs['prize_rank']  ,
			"user" => $_SESSION['USER_NAME'],
			"package" =>  '',
			"softname" => '',
			"gift" => '',
			'uid'=>$uid,
			"award_name" =>  $lottery_rs['prizename'],
			"is_luxury" => 3,//1豪华2普通3积分兑换
			'key' => 'lottery_success'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	//实物的所有兑换信息
	$arr = array(	
		'uid' => $uid,
		'pid' =>  $lottery_rs['prize_rank'],
		'prizename' => $lottery_rs['prizename'],
		'time' => date("Y-m-d",$time)
	);			
	$arr['username'] = str_replace_cn_new($_SESSION['USER_NAME'], 1, -2 );
	$key  = "christmas:{$active_id}_integral_kind_record:".$uid;
	$redis -> lPush($key,json_encode($arr));	
	$redis -> expire($key,86400*10);
	$array = array(
		'code' => 1,
		'pid' =>  $lottery_rs['prize_rank']  ,
		'prizename' => urlencode($lottery_rs['prizename']),
	);
	exit(json_encode($array));
}else{
	$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
	$login_url = $center_url."http://promotion.anzhi.com/lottery/christmas/integral_award.php?".$build_query;
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
		//用户积分、金额
		list($integral_num,$money) = get_user_integral($uid,$active_id);
		$tplObj -> out['integral_num'] = $integral_num ? $integral_num : 0;
		$tplObj -> out['money'] = $money ? $money : 0;
	}else{//未登录
		$tplObj -> out['is_login'] = 2;
	}
	//奖品
	$prize_info = array();
	for ($i = 0; $i <= 8; $i++) {
		$prize = get_prize_list($active_id,$i);
		$prize_info[$i] = $prize;
	}	
	$tplObj -> out['prize_list'] = $prize_info;	
	$tplObj -> out['top10_integral_award'] = get_top10_integral_award($active_id);
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['now'] = $time;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display('lottery/christmas/integral_award.html');	
}

