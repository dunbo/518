<?php

/*
** 抽奖接口
*/

include_once (dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
    'status' => 0,
    'info' => array()
);

if (!$imsi_status) {
    $return_arr['status'] = 300;
    echo json_encode($return_arr);;
    exit;
}

// 尝试将用户可抽奖次数-1
$reduce_ret = reduce_lottery_num_by_1();
if (!$reduce_ret) {
	$return_arr['status'] = 300;
	echo json_encode($return_arr);;
	exit;
}

//抽奖
load_helper('task');
$task_client = get_task_client();
$param_arr = array(
	'imsi' => $imsi,
	'aid' => $aid,
);
$task_ret = $task_client->do('guessappbattle_lottery', json_encode($param_arr));
$task_ret = json_decode($task_ret, true);

$the_award = $task_ret['award_level'];

// 可以抽奖，获得用户剩余抽奖次数
$now_num = get_lottery_num();

// 封装一下，判断中奖的名次是否大于中奖等级个数
$award_level_count = count($award_config);
$log_award = ($the_award > $award_level_count) ? 0 : $the_award;
//$the_award = $log_award;

$package = empty($task_ret['package'])? '' : $task_ret['package'];
$gift_card_no = empty($task_ret['gift_card_no'])? '' : $task_ret['gift_card_no'];
$gift_card_pwd = empty($task_ret['gift_card_pwd'])? '' : $task_ret['gift_card_pwd'];
$award_id = empty($task_ret['award_id'])? '' : $task_ret['award_id'];

$return_arr['status'] = 200;
$return_arr['info']['award_level'] = $the_award;
$return_arr['info']['left_num'] = $now_num;
$return_arr['info']['gift_card_no'] = $gift_card_no;
$return_arr['info']['gift_card_pwd'] = $gift_card_pwd;

$award_type = $award_config[$the_award]['type'];
if ($award_type == 2 || $award_type == 3) {
	// 礼包类奖品
	$gift_path = $award_config[$the_award]['gift_path'];
	$return_arr['info']['gift_path'] = $gift_path;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'award_level' => $log_award,
	'award_id' => $award_id,
	'package' => $package,
	'gift' => $gift_card_pwd,
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

// 准备返回
if ($the_award > $award_level_count) {
    // 没有中奖
    echo json_encode($return_arr);
    exit;
}

// 将中奖信息放在数组中，并返回
$return_arr['info']['award_level_name'] = $award_config[$the_award]['level_name'];
$return_arr['info']['award_name'] = $award_config[$the_award]['name'];
$return_arr['info']['award_type'] = $award_config[$the_award]['type'];
$return_arr['info']['package'] = $package;

echo json_encode($return_arr);
exit;