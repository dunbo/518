<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once (dirname(realpath(__FILE__)).'/superbowl_fun.php');
$model = new GoModel();
$active_info  = getActiveConfig();
$activity_config = json_decode($active_info['configcontent'],true);
$award_config = $activity_config['award_config'];
$active_id = $activity_config['activity_id'];

$return_arr = array(
		'status' => 0,
		'info' => array()
);
// 活动日志
$activity_log_file = "activity_{$active_id}.log";

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$imsi_status = 0;

//无sim卡
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
} else {
	$imsi_status = 1;
}
if($imsi_status == 0){
	//无sim卡
	$return_arr['status'] = 100;
	echo json_encode($return_arr);;
	exit;
}

// 判断版本是否大于等于5500
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];
$market_info = chk_market_version($alone_update,$cid);
//var_dump($market_info);
$market_info[0]=1;
if($market_info[0]!=1){
	//版本低
	$return_arr['status'] = 200;
	echo json_encode($return_arr);;
	exit;
}

$rkey_imsi_lottery_num = "superbowl_{$active_id}_{$imsi}_lottery_num";//用户可抽奖次数
//减少次数
$reduce =reduceLotteryNum();

if(!$reduce){
	// 没有抽奖机会
	$return_arr['status'] = 300;
	echo json_encode($return_arr);
	exit;
}

// 可以抽奖，获得用户剩余抽奖次数
$now_num = getLotteryNum();


//抽奖操作

load_helper('task');
$task_client = get_task_client();
$task_ret = $task_client->do('superbowl', $imsi);
$task_ret = json_decode($task_ret, true);

$the_award = $task_ret['award_level'];
$return_arr['status'] = 1;
$return_arr['info']['award_level'] = $the_award;
$return_arr['info']['left_num'] = $now_num;

// 判断中奖的名次是否大于中奖等级个数
$award_level_count = count($award_config);
$log_award = ($the_award > $award_level_count) ? 0 : $the_award;
permanentlog($activity_log_file, json_encode($log_award));
// 记日志
$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'award_level' => $log_award,
		'time' => time(),
		'key' => 'lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

// 准备返回
if ($the_award > $award_level_count||!$log_award) {
	// 没有中奖
	echo json_encode($return_arr);
	exit;
}

// 将中奖信息放在数组中，并返回
$return_arr['info']['award_level_name'] = mb_convert_encoding($award_config[$the_award - 1][0],"UTF-8");
$return_arr['info']['award_name'] = mb_convert_encoding($award_config[$the_award - 1][1],"UTF-8");
$return_arr['info']['award_type'] = $award_config[$the_award - 1][2];
echo json_encode($return_arr);
exit;