<?php
include_once ('./fun.php');
session_begin($sid);
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['is_login'] = 1;	
}else{//未登录 跳转到首页
	$url = $configs['activity_url']."lottery/ranking/index.php?aid={$active_id}&sid={$sid}";
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
list($ranking_config,$activity_arr) = get_config($active_id);		
$ranking_now_award_key = $prefix.":".$active_id.":now_award";
if($_GET){
	$tplObj -> out['static_url'] = $configs['static_url'];		
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['img_url'] = getImageHost();	
	$tplObj -> out['ranking_config'] = $ranking_config;	
	$ranking_now_award = $redis->get($ranking_now_award_key);
	$tplObj -> out['tab_sw'] = $ranking_now_award['sw_data'];		
	$tplObj -> out['tab_xn'] = $ranking_now_award['xn_data'];		
	$tplObj -> display('lottery/ranking/prize_list.html');
	exit;
}

$lottery_style_arr = array(
	1 => "老虎机",
	2 => "大转盘",
	3 => "九宫格",
);
$deduction_num = intval($_POST['continuity_lottery_num']); 
if($deduction_num<10) exit;
if($deduction_num != 10 && $deduction_num != 100){
	exit;
}
//点击抽奖日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"DEVICE_SN" => $_SESSION['DEVICE_SN'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	"award_level" => '',//pid
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	"type_lottery" =>$ranking_config['lottery_style'],
	"type_name" =>$lottery_style_arr[$ranking_config['lottery_style']],
	"deduction_num" =>$deduction_num,//使用抽奖数
	'key' => 'lottery'
);
//剩余抽奖次数
$rest_lottery_num_key = 'ranking_rest_lottery_num'.$uid.$active_id;
$rest_lottery_num = $redis->get($rest_lottery_num_key);
if($rest_lottery_num < $deduction_num){
	$log_data['msg'] = '剩余抽奖次数不足';
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
}
$now_num = $redis->setx('incr','ranking_rest_lottery_num'.$uid.$active_id,-$deduction_num);
if($now_num < 0){
	$log_data['msg'] = '剩余抽奖次数不足';
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	$redis->set('ranking_rest_lottery_num'.$uid.$active_id,0,10*60);
	exit(json_encode(array('code'=>0,'msg'=>'剩余抽奖次数不足')));
}	

load_helper('task');
$task_client = get_task_client();
$new_array = array();
$new_array['uid'] =$uid;
$new_array['aid'] =$active_id;
$new_array['activityName'] =$activity_arr['name'];
$new_array['imsi'] = $_SESSION['USER_IMSI'];
$new_array['username'] = $_SESSION['USER_NAME'];
$new_array['lottery_num'] = $deduction_num;
$the_award = $task_client->do('ranking_lottery', json_encode($new_array));
$lottery_res = json_decode($the_award,true);		
//用户已用抽奖次数+1
save_deduction_num($uid,$active_id,$deduction_num);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
list($prize_name,$new_prize_level) = get_prize_name($active_id);
$no_lottery = 0;
$succ_sw_str = '';
$succ_xn_str = '';
$err_str = '';
$is_kind = 0;
$i = 1;
$ii = 1;
foreach($lottery_res as $k => $v){
	if($v == -1 || $v['type'] == 3){
		$no_lottery = 1;
		continue;
	}
	
	//抽奖成功日志
	$log_data = array(
			"imsi" => $v['imsi'],
			"device_id" => $_SESSION['DEVICEID'],
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"award_level" => $v['level'],//pid
			"user" => $_SESSION['USER_NAME'],
			"package" =>  ($v['type'] ==2||$v['type'] ==5)  ? $v['package'] : '',
			"softname" => ($v['type'] ==2||$v['type'] ==5) ? $v['softname'] : '',
			"gift" => ($v['type'] ==2||$v['type'] ==5)  ? $v['gift_number'] : '',
			'uid'=>$uid,
			'type'=>$v['type'],//1实物2礼包3谢谢参与4礼券5礼包（直接发放）
			"type_lottery" =>$ranking_config['lottery_style'],
			"type_name" =>$lottery_style_arr[$ranking_config['lottery_style']],			
			"award_name" => $v['name'],
			"deduction_num" =>$deduction_num,//属于10连抽或100连抽中奖
			'key' => 'award'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	if( $v['type'] ==2){
		//用户中奖信息
		$arr = array(	
			'first_text' => $v['gift_number'],
			'uid' => $uid,
			'third_text' => $v['package'] ,
			'second_text' => $v['softname'],
		);
		//礼包的所有兑换信息
		$redis -> lPush("ranking_gift_prize{$uid}{$active_id}",json_encode($arr),1800);	
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $v['pid'],
			'prizename_old' => $v['name'],
			'type' => $v['type'],			
			'time' => date("Y-m-d",$time)
		);		
		if($v['type'] ==5){
			$arr['gift_code'] = $v['gift_number'];
			$arr['prizename'] = $v['softname'];
		}	
		$redis -> lPush("ranking_draw_award{$uid}{$active_id}",json_encode($arr),1800);
	}
	if($v['type'] == 1){
		$succ_sw_str .= "<li><p>".$i++ ."、恭喜您获得了<span>《".$v['name']."》</span>，请尽快完善个人信息，以免造成奖品无法发送 </p></li>";
		$is_kind = 1;
	}else {
		if($v['type'] == 4){
			$succ_xn_str .= "<li><p>".$ii++ ."、恭喜您获得了<span>《".$v['name']."》</span>，礼券有效期为3天，请尽快使用 </p></li>";
		}else if($v['type'] == 2 || $v['type'] == 5){
			if($_SESSION['VERSION_CODE'] >= 6410){
				$copy_str = "<a href='javascript:;' onclick='copytext(\"{$v['gift_number']}\");' >复制</a>";
			}
			$succ_xn_str .= "<li><p>".$ii++ ."、恭喜您获得了<span>《".$v['name']."》</span>，礼包码为：".$v['gift_number'].$copy_str." </p></li>";
		}
	}
}
//抽奖成功日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"DEVICE_SN" => $_SESSION['DEVICE_SN'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	"award_level" => '',//pid
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	"type_lottery" =>$ranking_config['lottery_style'],
	"type_name" =>$lottery_style_arr[$ranking_config['lottery_style']],
	"deduction_num" =>$deduction_num,//使用抽奖数
	'key' => 'lottery_success'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

if($succ_sw_str != '' || $succ_xn_str != ''){
	$ret_arr = array(
		'code' => 1,
		'is_kind' => $is_kind,
		'sw_data' => $succ_sw_str ? $succ_sw_str : '<li><p style="padding: 70px 0 0; text-align: center" >'.$ranking_config['not_winning_tips'].'</p></li>' ,
		'xn_data' => $succ_xn_str ? $succ_xn_str : '<li><p style="padding: 70px 0 0; text-align: center">'.$ranking_config['not_winning_tips'].'</p></li>' ,
	);
	$redis->set($ranking_now_award_key,$ret_arr,10*60);
	exit(json_encode($ret_arr));
}
if($no_lottery == 1){
	exit(json_encode(array('code'=>0,'msg'=>$ranking_config['not_winning_tips'])));
}


