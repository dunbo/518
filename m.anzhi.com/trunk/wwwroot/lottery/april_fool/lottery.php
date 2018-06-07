<?php
include_once ('./fun.php');
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}	
$build_query = http_build_query($_GET);
session_begin($sid);
if($_POST){
	if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		$uid = $_SESSION['USER_UID'];
	}else{//未登录 跳转到首页
		$url = "http://promotion.anzhi.com/lottery/{$prefix}/lottery.php?".$build_query;
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}	
	$is_luxury = 1;
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
		"is_luxury" => $is_luxury,//1转盘抽奖2下载抽奖
		'key' => 'lottery'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	//剩余抽奖次数
	$now_num = $redis->setx('incr',"{$prefix}:{$active_id}:rest_num:".$uid,-1);
	if($now_num < 0){
		$redis->set("{$prefix}:{$active_id}:rest_num:".$uid,0);
		exit(json_encode(array('code'=>0,'msg'=>'抱歉，您账号今日可用抽奖次数已用完！','title'=>"【抽奖次数已用完】")));
	}		
	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'is_luxury' => $is_luxury,
	);	
	$the_award = $task_client->do("{$prefix}_lottery", json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);		
	//用户已用抽奖数据
	save_deduction($uid,$active_id,$_SESSION['USER_NAME'],$is_luxury);
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
			"package" => '',
			"softname" => '',
			"gift" =>   '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['pid'] ==0 ? "礼包" : $lottery_rs['prizename'],
			"is_luxury" => $is_luxury,//1转盘抽奖2下载抽奖
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
	if($lottery_rs['prize_rank'] == 8){
		$key = "{$prefix}:{$active_id}:recording:".$uid;
	}else{
		$key = "{$prefix}:{$active_id}:lottery_record:".$uid;
	}
	$redis -> lPush($key,json_encode($arr));	
	$redis -> expire($key,86400*10);

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
	$login_url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/lottery.php?".$build_query;
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
		$tm_config = get_config($active_id,$uid);
		$tplObj -> out['tm_config'] = $tm_config;
		if($tm_config[date('Y-m-d',$time)]['status'] == 1){
			$tplObj -> out['is_sing'] = 1;//当天是否签到过(1是2否)
		}else{
			$tplObj -> out['is_sing'] = 2;//当天是否签到过
		}	
		$tplObj -> out['num'] = $tm_config[date('Y-m-d',$time)]['num'];	
		$i = 0;
		foreach($tm_config as $k => $v){
			if($v['num'] && $v['status'] == 1){
				$i++;
			}
		}	
		$tplObj -> out['sign_num'] = $i;		
		list($recording,$kind_award_list,$down_lottery_record) = get_user_lottery_record($uid,$active_id);
		$award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","xy2_draw_gift");			
		if(!$recording && !$kind_award_list && !$down_lottery_record && !$award_gift){
			$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
		}else{
			$tplObj -> out['is_user_winning'] = 1;
		}
		//用户抽奖次数
		$lottery_num = get_user_num($uid,$active_id,1);
		$tplObj -> out['lottery_num'] = $lottery_num ? $lottery_num : 0;
		//用户金额
		$money = $redis->get("april_fool:".$active_id.":money:".$uid);	
		//今日金额	
		$today_money = $redis->get("april_fool:".$active_id.":today_money:".$uid);			
		$tplObj -> out['money'] = $money ? $money : 0;
		$tplObj -> out['today_money'] = $today_money ? $today_money : 0;
	}else{//未登录
		$tplObj -> out['is_login'] = 2;
		$tplObj -> out['lottery_num'] = 5;	
	}
	//跑马灯（最新30条数据）
	$tplObj -> out['top10_lottery'] = get_top30_lottery($active_id,1);
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['now'] = $time;	
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> display("lottery/{$prefix}/lottery.html");	
}

