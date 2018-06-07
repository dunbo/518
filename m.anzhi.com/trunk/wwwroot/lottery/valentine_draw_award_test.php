<?php

include_once ('./valentine_fun.php');
session_begin();
$active_id = 273;
	
$imsi_arr = array("460042359383380","460086504303738","460011705796755","460066600163811","460073760208920","460092178990235","460034351743375","460086530305114","460024872225457","460071144031826","460030233084908","460088584916656","460013804672458","460013855218561","460039294304493","460073258131998","460070875446228","460059990915940","460015370965804","460094962560934","460092343491874","460090393188926","460086690816967","460053412941116","460039791800077","460059800300104","460086622319091","460087246883404","460078192938724","460078140494581","460019626873075","460058071021740","460034240615977","460025858003948","460099604764641","460043462001704","460073659513597","460043237969651","460046116390344","460070159321734","460055352379735","460097129613822","460016603109971","460013098810431","460040402766596","460094160059713","460082125214678","460066191885583","460065071217878","460059791540009");

$uid_arr = array("20150625081919h5iqf3zNfn","20150625101643BK636uTvIQ","20150625153820t94NX5799j","20150625100001pOi9JN14Ck","201506251025439m2i6z5stp","20150625083857HBScI6R415","20150625074217QBk1AJVRNz","20150625074653xJcUF36W6L","20150625101350my5BegfwmS","20150625095510SPPA1JG2jx","20150625082524ypjTh8x6Ac","20150625101014vw96rN9zu5","201506250949229X2d3c4L56","201506250949408x9F0bgHlm","20150625091047b7CMDTJ5Wy","20150625081212NQBqk6yO5m","20150625102231heCDo1xI9O","201506250912203OK6bjQLdt","20150625092838osE5OeCNTs","20150625091513791KUM3Ew2","20150625191747fNnH44Np1F","20150625083223v1VPpJ9EXE","20150625080517gjX0fd1jk2","201506250858066aye6cWMgG","2015062509151667c8da1RwP","201506250935463ph8IVLs9L","201506251905432iw2bSQC3S","201506250812230W18dD7j24","20150625103731SAaTh8BQu9","20150625075145DgQQcIWnYB","20150625101441CQa72mmvg3","20150625102301TlHFB3TEpS","20150625191245q0hEyi9801","20150625101515wHG8CXh5cq","201506250750404xRjluhI0u","2015062507591190qPzfr5w5","20150625191142KKf89SP87L","201506250921598dWcVog4V7","20150625091421aS52kO5kXm","20150625094019n4oBhn59P0","20150625090727nL42yLMN5a","20150625092524SqrFN6b6H7","20150625103211L3zjEbLyfe","20150625084057RU74RjBX7L","201506250826033GxCpS2ihq","20150625082548RpA532EXkR","2015062509251650fTzGK5HD","2015062508001768125Whbcw","20150625083917NSVNjDA6q6","20150625083948y5TJ1n875p");

foreach($uid_arr as $v){
	get_rest_valentine($v);
}
exit;

$r = array_rand(range(0, 49));
$imsi = $imsi_arr[$r];
$uid = $uid_arr[$r];
$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => '',
		"time" => time(),
		"award_level" => '',//pid
		"user" => $_SESSION['USER_NAME'],
		"package" => '',
		"softname" => '',
		"gift" =>  '',
		'uid'=>$uid ,
		"award_name" => '',
		'key' => 'lottery'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
if($imsi && $uid){
	//剩余抽奖次数
	list($userinfo,$rest_num) =get_rest_valentine($uid);
	$now_num = $redis->setx('incr','valentine_rest_num'.$uid,-1);
	//var_dump($userinfo,$rest_num,$now_num);
	if($rest_num <= 0 || $now_num < 0){
		$redis->set('valentine_rest_num'.$uid,0,30*60);
		exit('剩余抽奖次数不足');
	}	
	//用户已用抽奖次数+1
	save_deduction_num($uid);
	
	load_helper('task');
	$task_client = get_task_client();
	$new_array = array();
	$new_array['uid'] =$uid;
	$new_array['username'] = $_SESSION['USER_NAME'];
	$gift_prize_list = $redis -> getlist("valentine_gift_prize:{$uid}");
	if(!$gift_prize_list){
		$new_array['package'] = $_POST['pkg'];	
	}
	$the_award = $task_client->do('valentine', json_encode($new_array));
	$lottery_rs = json_decode($the_award,true);		
	if($lottery_rs['pid'] == 0){
		$gift_info = json_decode($lottery_rs['gift_number'],true);
	}		
	//file_put_contents('/tmp/valentine.log',$lottery_rs);
	//抽奖成功日志
	$log_data = array(
			"imsi" => $imsi,
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => '',
			"time" => time(),
			"award_level" => $lottery_rs['pid'],//pid
			"user" => $_SESSION['USER_NAME'],
			"package" => $lottery_rs['pid'] ==0 ? $gift_info['package'] : '',
			"softname" => $lottery_rs['pid'] ==0 ? $gift_info['softname'] : '',
			"gift" =>  $lottery_rs['pid'] ==0 ? $gift_info['gift_number'] : '',
			'uid'=>$uid,
			"award_name" => $lottery_rs['prizename'],
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
			'update_tm' => time(),
		);
		//礼包的所有兑换信息
		$redis -> lPush("valentine_gift_prize:{$uid}",json_encode($arr));	
	}else{
		//实物的所有兑换信息
		$arr = array(	
			'uid' => $uid,
			'pid' =>  $lottery_rs['pid'],
			'prizename' => $lottery_rs['name'],
			'time' => $time
		);			
		$redis -> lPush("valentine_kind_award:{$uid}",json_encode($arr));
	}
	$array = array(
		'uid' => $uid,
		'code' => '成功',
		'pid' => $lottery_rs['pid'],
		'package' => $gift_info['package'] ,
		'softname' => $gift_info['softname'],
		'gift_num' => $gift_info['gift_number'],
		'prizename' => $lottery_rs['name'],
	);
	print_r($array);
	exit;
}
