<?php
include_once ('./fun.php');
$sid = $_POST['sid'];
session_begin();
$time = time();
$active_id = 409;
$imsi_arr = array("460042359383380","460086504303738","460011705796755","460066600163811","460073760208920","460092178990235","460034351743375","460086530305114","460024872225457","460071144031826","460030233084908","460088584916656","460013804672458","460013855218561","460039294304493","460073258131998","460070875446228","460059990915940","460015370965804","460094962560934","460092343491874","460090393188926","460086690816967","460053412941116","460039791800077","460059800300104","460086622319091","460087246883404","460078192938724","460078140494581","460019626873075","460058071021740","460034240615977","460025858003948","460099604764641","460043462001704","460073659513597","460043237969651","460046116390344","460070159321734","460055352379735","460097129613822","460016603109971","460013098810431","460040402766596","460094160059713","460082125214678","460066191885583","460065071217878","460059791540009");

$uid_arr = array("20150625081919h5iqf3zNfn","20150625101643BK636uTvIQ","20150625153820t94NX5799j","20150625100001pOi9JN14Ck","201506251025439m2i6z5stp","20150625083857HBScI6R415","20150625074217QBk1AJVRNz","20150625074653xJcUF36W6L","20150625101350my5BegfwmS","20150625095510SPPA1JG2jx","20150625082524ypjTh8x6Ac","20150625101014vw96rN9zu5","201506250949229X2d3c4L56","201506250949408x9F0bgHlm","20150625091047b7CMDTJ5Wy","20150625081212NQBqk6yO5m","20150625102231heCDo1xI9O","201506250912203OK6bjQLdt","20150625092838osE5OeCNTs","20150625091513791KUM3Ew2","20150625191747fNnH44Np1F","20150625083223v1VPpJ9EXE","20150625080517gjX0fd1jk2","201506250858066aye6cWMgG","2015062509151667c8da1RwP","201506250935463ph8IVLs9L","201506251905432iw2bSQC3S","201506250812230W18dD7j24","20150625103731SAaTh8BQu9","20150625075145DgQQcIWnYB","20150625101441CQa72mmvg3","20150625102301TlHFB3TEpS","20150625191245q0hEyi9801","20150625101515wHG8CXh5cq","201506250750404xRjluhI0u","2015062507591190qPzfr5w5","20150625191142KKf89SP87L","201506250921598dWcVog4V7","20150625091421aS52kO5kXm","20150625094019n4oBhn59P0","20150625090727nL42yLMN5a","20150625092524SqrFN6b6H7","20150625103211L3zjEbLyfe","20150625084057RU74RjBX7L","201506250826033GxCpS2ihq","20150625082548RpA532EXkR","2015062509251650fTzGK5HD","2015062508001768125Whbcw","20150625083917NSVNjDA6q6","20150625083948y5TJ1n875p");

$user_arr = array("zaitest1000000","zaitest1000006","zaitest1000042","zaitest1000063","zaitest1000073","zaitest1000075","zaitest1000123","zaitest1000127","zaitest1000136","zaitest1000166","zaitest1000207","zaitest1000220","zaitest1000223","zaitest1000260","zaitest1000267","zaitest1000300","zaitest1000314","zaitest1000315","zaitest1000371","zaitest1000408","zaitest100049","zaitest1000509","zaitest1000533","zaitest1000564","zaitest1000565","zaitest1000577","zaitest1000581","zaitest1000592","zaitest1000594","zaitest1000633","zaitest1000653","zaitest100068","zaitest1000685","zaitest1000736","zaitest1000751","zaitest1000757","zaitest1000809","zaitest1000821","zaitest1000822","zaitest1000836","zaitest1000853","zaitest1000867","zaitest1000870","zaitest1000912","zaitest1000921","zaitest1000926","zaitest1000946","zaitest1000967","zaitest1001009","zaitest1001018");
$r = array_rand(range(0, 49));
$imsi = $imsi_arr[$r];
$uid = $uid_arr[$r];
$user_name = $user_arr[$r];
//抽奖日志
$log_data = array(
	"imsi" => $imsi,
	"device_id" => 'test',
	"activity_id" => $active_id,
	"ip" => '203test',
	"sid" => '203test',
	"time" => $time,
	"award_level" => '',//pid
	"user" => $user_name,
	'uid'=>$uid,
	"award_name" =>'',
	"is_luxury" =>3,//1豪华2普通3积分兑换
	'key' => 'lottery'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
$position = rand(0,8);
$prize_info = get_prize_list($active_id,$position);
$prize_integral = intval($prize_info['prize_integral']);
//剩余积分不足
$now_integral_num = $redis->get("christmas:{$active_id}_rest_integral:".$uid);
if($now_integral_num < $prize_integral){
	exit('您账号当前可用积分不足，请尽快充值获取积分！');
}	
if($prize_info['sold_out'] == 1 && $prize_info['start_time'] > $time){
	exit('兑换时间还没开始请耐心等待！');
}	
//缓存锁
$lock = $redis->get("christmas:{$active_id}_lock:".$position);
if($lock){
	exit('兑换时间还没开始请耐心等待！');
}
$redis->set("christmas:{$active_id}_lock:".$position,1,600);
$integral_num = $redis->setx('incr',"christmas:{$active_id}_rest_integral:".$uid,-$prize_integral);

load_helper('task');
$task_client = get_task_client();
$new_array = array(
	'uid' => $uid,
	'aid' => $active_id,
	'username' => $user_name,
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
		"imsi" =>  $imsi,
		"device_id" => 'test',
		"activity_id" => $active_id,
		"ip" => 'test',
		"sid" => 'tset',
		"time" => $time,
		"award_level" =>  $lottery_rs['prize_rank']  ,
		"user" => $user_name,
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
$redis -> lPush("christmas:{$active_id}_integral_kind_record:".$uid,json_encode($arr));	
$array = array(
	'code' => 1,
	'pid' =>  $lottery_rs['prize_rank']  ,
	'prizename' => $lottery_rs['prizename'],
);
var_dump($array);
exit;