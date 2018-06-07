<?php
/*
** 活动抽奖接口
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

// 记录抽奖按钮日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => $now,
	'type_lottery'=> 5,
	'type_name'=> '其他',
    'key' => 'lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

$return_arr = array(
	'status' => 0,
	'info' => array()
);

if (!$imsi_status) {
    $return_arr['status'] = 300; //没插sim卡
    echo json_encode($return_arr);
    exit;
}

// 尝试将用户可抽奖次数-1
$decr_ret = decr_lottery_num();
if (!$decr_ret) {
	$return_arr['status'] = 301; //没有抽奖次数
	echo json_encode($return_arr);
	exit;
}
// 可以抽奖，获得用户剩余抽奖次数
$now_num = get_lottery_num();

$ret = get_brush_all($aid, 1); //验证是否在黑名单
if ($ret['code'] == 0) {
	// 黑名单的抽奖不中
	$award = -1;
	$brush = 1;
} else if ($ret['code'] == 1) {
	$brush = 2;
	// 调用WORK抽奖
	load_helper('task');
	$task_client = get_task_client();
	$prefix = "custom_imsi";
	$new_array = array(
		'aid' => $aid,
		'imsi' => $imsi,
		'prefix' => $prefix,
	);
	$task_ret = $task_client->do('custom_imsi_lottery', json_encode($new_array));
	if ($task_ret == -1) {
		// 没中奖
		$award = -1;
	} else {
		$task_ret = json_decode($task_ret, true);
		$award = $task_ret['prize_rank'];
		$award_id = $task_ret['award_id'];
	}
}

$return_arr['status'] = 200;
$return_arr['info']['award_level'] = $award; //中奖等级 1,2,3...
$return_arr['info']['left_num'] = $now_num; //剩余抽奖次数
$return_arr['info']['award_id'] = $award_id; //中奖序号

// 抽到奖品记日志
if ($award && $award != -1) {
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'award_level' => $award,
		'time' => time(),
		'type_lottery'=> 5,
		'type_name'=> '其他',
		'brush' => $brush,
		'award_id' => $award_id, //中奖纪录在表里的id
		'key' => 'object_award'
	);
	permanentlog($activity_log_file, json_encode($log_data));
}

if ($award < 0) {
    // 没有中奖
    echo json_encode($return_arr);
    exit;
} else {
    set_brush_byip($aid); //根据ip防刷
	// 将中奖信息返回
	$return_arr['info']['award_level_name'] = $award_level_name_arr[$award]; //奖品等级 一等奖,二等奖...
	$return_arr['info']['award_name'] = get_prize_name($award); //奖品名称
	$return_arr['info']['award_type'] = 1; //奖品类型
	echo json_encode($return_arr);
	exit;
}