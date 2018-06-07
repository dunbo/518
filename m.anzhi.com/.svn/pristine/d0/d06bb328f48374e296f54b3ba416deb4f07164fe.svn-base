<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$active_id =263;//278 518test
$model = new GoModel();
//没有session 跳转到首页
session_begin();
$sid = $_GET['sid']?$_GET['sid']:$_POST['sid'];
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];    	
}else{//未登录 跳转到首页
	header("Location: http://promotion.anzhi.com/lottery/integral.php?sid={$sid}");        
}
$userinfo = $redis->get('integral_userinfo'.$uid);	
if($_POST){
	$rank = $_POST['rank'];
	$now_num_begin = $redis->gethash("integral_prize:{$rank}");
    if(empty($now_num_begin)){
		header("Location: http://promotion.anzhi.com/lottery/integral.php?sid={$sid}");   
    }	
	$rest_integral = $redis->get('rest_integral'.$uid);
	if($rest_integral < $now_num_begin['prize_integral']){
		exit(json_encode(array('code'=>0,'msg'=>'您当前可用的积分不足，请充值获取积分')));
	} 	
	$exchange_num = $redis->get('integral_exchange_num'.$uid);
	if($exchange_num <= 0){
		$r_tm = strtotime(date('Y-m-d').' 23:59:59')-time();
		$redis->set('integral_exchange_num'.$uid,0,$r_tm);
		exit(json_encode(array('code'=>0,'msg'=>'您今天可用兑换次数已经用完')));
	}	
	if($now_num_begin['num'] <= 0){
		$redis->setx("HSET","integral_prize:{$rank}","num",0);
		exit(json_encode(array('code'=>0,'msg'=>'该奖品已经被兑换完')));
	}
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array();
	$new_array['uid'] =$uid;
	$new_array['rank'] =$rank;
	$new_array['username'] = $_SESSION['USER_NAME'];
	$new_array['type'] = 1;
	$the_award = $task_client->do('integral_work', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);	
	if($lottery_rs['code'] == 0 ){
		exit(json_encode(array('code'=>0,'msg'=>$lottery_rs['msg'])));
	}
	//兑换成功日志
	$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => time(),
			"award_level" => $now_num_begin['id'],//pid
			"user" => $_SESSION['USER_NAME'],
			"name" => $userinfo['contact_name'],
			"telphone" => $userinfo['phone'],
			"address" => $userinfo['address'],
			"package" => "",
			"gift" =>  "",
			"users" => '',
			'uid'=>$uid,
			"lottery_type" => 1,//1实物2礼包
			"award_name" => $lottery_rs['prizename'],
			'key' => 'integral_success'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	exit(json_encode(array('code'=>1)));
}else{
	if($_GET['log'] == 1){
		//兑换日志
		$log_data = array(
				"imsi" => $_SESSION['USER_IMSI'],
				"device_id" => $_SESSION['DEVICEID'],
				"activity_id" => $active_id,
				"ip" => $_SERVER['REMOTE_ADDR'],
				"sid" => $sid,
				"time" => time(),
				"award_level" => $_GET['pid'],//pid：奖品id 0礼包
				"user" => $_SESSION['USER_NAME'],
				"name" => $userinfo['contact_name'],
				"telphone" => $userinfo['phone'],
				"address" => $userinfo['address'],
				"package" => "",
				"gift" =>  "",
				"users" => '',
				'uid'=>$uid,
				"lottery_type" => $_GET['lottery_type'],//1实物2礼包
				"award_name" => $_GET['prizename'],
				'key' => 'integral'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	}
	
}

