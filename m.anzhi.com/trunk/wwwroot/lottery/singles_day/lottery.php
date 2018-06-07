<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}

if($_POST){
	//抽奖日志
	$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => '',//pid
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"award_name" =>'',
		'key' => 'lottery'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	//剩余抽奖次数
	$lottery_num_key = $prefix.":".$active_id.":lottery_num:".$imsi;
	$now_num = $redis->setx('incr',$lottery_num_key,-1);
	if($now_num < 0){
		$redis->set($lottery_num_key,0,10*60);
		exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
	}	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array();
	$new_array['aid'] =$active_id;
	$new_array['prefix'] = $prefix;
	$new_array['imsi'] = $imsi;
	$the_award = $task_client->do('custom_imsi_lottery', json_encode($new_array));
	if($the_award == -1){
		$array = array(	'code' => 0,'msg'=>'未中奖');		
		exit(json_encode($array));
	}
	$lottery_rs = json_decode($the_award,true);	
	set_lottery_num("`lottery_num`-1");
	//抽奖成功日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => $lottery_rs['prize_rank'],
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"award_name" => $lottery_rs['name'],
		'key' => 'award'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$my_prize_key = $prefix.":".$active_id.':my_prize:'.$imsi;
	$redis->delete($my_prize_key);		
	$array = array(
		'code' => 1,
		'award_id' => $lottery_rs['award_id'],
		'pid' => $lottery_rs['pid'],
		'prize_rank' => $lottery_rs['prize_rank'],
		'prizename' => $lottery_rs['prizename'],
	);
	exit(json_encode($array));
}else{
	if($_GET['is_down_num']){
		$pkg = $_GET['pkg'];
		$num = add_lottery_num(2,$pkg);
		echo $num;
		exit;
	}else if($_GET['is_share_num']){
		$num = add_lottery_num(1);
		echo $num;
		exit;		
	}
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tplObj -> out['prefix'] = $prefix;	
	$soft_info = get_soft_info($configs['is_test']);
	$tplObj -> out['soft_info']  =  $soft_info;
	$tplObj -> out['lottery_num']  =  get_lottery_num();	
	$tplObj -> out['lottery_prize']  =  get_lottery_prize();	
	$tpl = "lottery/".$prefix."/lottery.html";
	$tplObj -> display($tpl);	
}

