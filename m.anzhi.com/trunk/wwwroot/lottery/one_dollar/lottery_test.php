<?php
include_once ('./one_dollar_fun.php');
$sid = $_POST['sid'];
session_begin();
$active_id = 297;
	
$imsi_arr = array("460042359383380","460086504303738","460011705796755","460066600163811","460073760208920","460092178990235","460034351743375","460086530305114","460024872225457","460071144031826","460030233084908","460088584916656","460013804672458","460013855218561","460039294304493","460073258131998","460070875446228","460059990915940","460015370965804","460094962560934","460092343491874","460090393188926","460086690816967","460053412941116","460039791800077","460059800300104","460086622319091","460087246883404","460078192938724","460078140494581","460019626873075","460058071021740","460034240615977","460025858003948","460099604764641","460043462001704","460073659513597","460043237969651","460046116390344","460070159321734","460055352379735","460097129613822","460016603109971","460013098810431","460040402766596","460094160059713","460082125214678","460066191885583","460065071217878","460059791540009");

$uid_arr = array("20150625081919h5iqf3zNfn","20150625101643BK636uTvIQ","20150625153820t94NX5799j","20150625100001pOi9JN14Ck","201506251025439m2i6z5stp","20150625083857HBScI6R415","20150625074217QBk1AJVRNz","20150625074653xJcUF36W6L","20150625101350my5BegfwmS","20150625095510SPPA1JG2jx","20150625082524ypjTh8x6Ac","20150625101014vw96rN9zu5","201506250949229X2d3c4L56","201506250949408x9F0bgHlm","20150625091047b7CMDTJ5Wy","20150625081212NQBqk6yO5m","20150625102231heCDo1xI9O","201506250912203OK6bjQLdt","20150625092838osE5OeCNTs","20150625091513791KUM3Ew2","20150625191747fNnH44Np1F","20150625083223v1VPpJ9EXE","20150625080517gjX0fd1jk2","201506250858066aye6cWMgG","2015062509151667c8da1RwP","201506250935463ph8IVLs9L","201506251905432iw2bSQC3S","201506250812230W18dD7j24","20150625103731SAaTh8BQu9","20150625075145DgQQcIWnYB","20150625101441CQa72mmvg3","20150625102301TlHFB3TEpS","20150625191245q0hEyi9801","20150625101515wHG8CXh5cq","201506250750404xRjluhI0u","2015062507591190qPzfr5w5","20150625191142KKf89SP87L","201506250921598dWcVog4V7","20150625091421aS52kO5kXm","20150625094019n4oBhn59P0","20150625090727nL42yLMN5a","20150625092524SqrFN6b6H7","20150625103211L3zjEbLyfe","20150625084057RU74RjBX7L","201506250826033GxCpS2ihq","20150625082548RpA532EXkR","2015062509251650fTzGK5HD","2015062508001768125Whbcw","20150625083917NSVNjDA6q6","20150625083948y5TJ1n875p");

$r = array_rand(range(0, 49));
$imsi = $imsi_arr[$r];
$uid = $uid_arr[$r];
$time = time();

//奖品
$i = rand(1, 12);
$prize_info = get_prize($i);

$num = rand(1, 50);
if($num == 50){
	$num = "包尾";
}
//抢夺日志
$log_data = array(
	"imsi" => $imsi,
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => '',
	"time" => $time,
	"award_level" => $prize_info['id'],//pid
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	"award_name" => $prize_info['prizename'],
	"start_time" => $prize_info['start_time'],//抢夺开始时间
	'key' => 'lottery'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
$rest_integral = get_rest_integral($uid);
if($rest_integral <= 0){
	exit('您当前积分不足，请您充值后再抢夺');
} 		
if($prize_info['start_time'] > $time){
	exit($prize_info['prizename'].'时间未开始');
}	
if($prize_info['num'] == 0){
	exit(json_encode(array('code'=>0,'msg'=>'该奖品已经售馨')));
} 	
load_helper('task');
$task_client = get_task_client();
$new_array = array(
	'uid' => $uid,
	'username' => $_SESSION['USER_NAME'],
	'start_time' => $prize_info['start_time'],
	'pid' => $prize_info['id'],
	'prize_integral' => $prize_info['prize_integral'],
	'prizename' => $prize_info['prizename'],
	'num' => $num,
	'position' => $i,
);
$the_award = $task_client->do('one_dollar_worker', json_encode($new_array));
$lottery_rs = json_decode($the_award,true);	
if($lottery_rs['code'] == 0 ){
	exit($lottery_rs['msg']);
}
//兑换成功日志
$log_data = array(
	"imsi" =>  $imsi,
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => '',
	"time" => $time,
	"award_level" => $lottery_rs['pid'],//pid
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	"award_name" => $prize_info['prizename'],
	'key' => 'lottery_success'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
if($lottery_rs['code'] == 1 && $lottery_rs['status'] == 1 ){
	//中奖日志
	$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => '',
		"time" => $time,
		"award_level" => $lottery_rs['pid'],//pid
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$lottery_rs['uid'],
		"award_name" => $prize_info['prizename'],
		'key' => 'lottery_success_user'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
}	
exit(json_encode(array('code'=>1,'msg'=> $prize_info['prizename'])));

