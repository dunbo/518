<?php
include_once ('./fun.php');


$tm_con = sign_config($uid);
$now_day = $_POST['repair_date'];
//用户补签日志
$log_data = array(
	'time'	=>	$time,
	'date' => $now_day,//用户补签哪一天的数据	
	'imsi'	=>	$_SESSION['USER_IMSI'],
	'uid'	=>	$uid,
	'sid'	=>	$sid,
	'username'	=>	$_SESSION['USER_NAME'],
	'device_id'	=>	$_SESSION['DEVICEID'],
	"mac" => $_SESSION['MAC'],
	"mid"	=>	$m_arr['id'],
	'key'	=>	'retroactive'
);

if($tm_con[$now_day]['status'] >= 1){
	$log_data['msg'] = '您本日已签到过';
	permanentlog($log_key, json_encode($log_data));
	exit(json_encode(array('code'=>0,'msg'=>'您本日已签到过')));
}
if( empty($tm_con[$now_day]) ){
	$log_data['msg'] = '签到日期已过或未开始';
	permanentlog($log_key, json_encode($log_data));	
	exit(json_encode(array('code'=>0,'msg'=>'签到日期已过或未开始')));
}
//补签卡数量 
$cards_num = get_cards_num($uid);
if($cards_num <=0 ){
	$log_data['msg'] = '补签卡数量为0，无法进行补签';
	permanentlog($log_key, json_encode($log_data));		
	exit(json_encode(array('code'=>0,'msg'=>'补签卡数量为0，无法进行补签')));
}
$cards_num_key = "{$prefix}:{$m_arr['id']}:res_cards_num:".$uid;
$now_cards_num = $redis -> setx('incr',$cards_num_key, -1);
if($now_cards_num < 0){
	$redis -> set($cards_num_key,0,1800);
	$log_data['msg'] = '补签卡数量为0，无法进行补签';
	permanentlog($log_key, json_encode($log_data));		
	exit(json_encode(array('code'=>0,'msg'=>'补签卡数量为0，无法进行补签')));
}
//用户补签卡已使用数+1
save_deduction_num($uid,"used_cards_num");	
	
if($tm_con[$now_day]['level']) {	
	$tm_con[$now_day]['status'] = 2;
	$sign_redis->set("{$prefix}:{$m_arr['id']}:tm_config:".$uid, $tm_con, 86400*60);
}
//添加用户补签数据
add_sign_data($uid,$now_day,2);
permanentlog($log_key, json_encode($log_data));
$ret_arr = array(
	'code' => 1,
	'msg' => '补签成功',
);

exit(json_encode($ret_arr));