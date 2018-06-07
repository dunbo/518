<?php
/*
** 抽奖接口
*/

include_once (dirname(realpath(__FILE__)).'/octoberflight_init.php');

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
// 可以抽奖，获得用户剩余抽奖次数
$now_num = get_lottery_num();

//抽奖
load_helper('task');
$task_client = get_task_client();
$task_ret = $task_client->do('octoberflight_lottery', $imsi);
$task_ret = json_decode($task_ret, true);

$the_award = $task_ret['award_level'];
$package = empty($task_ret['package'])? '' : $task_ret['package'];
$gift_card_no = empty($task_ret['gift_card_no'])? '' : $task_ret['gift_card_no'];
$gift_card_pwd = empty($task_ret['gift_card_pwd'])? '' : $task_ret['gift_card_pwd'];

$return_arr['status'] = 200;
$return_arr['info']['award_level'] = $the_award;
$return_arr['info']['left_num'] = $now_num;
$return_arr['info']['gift_card_no'] = $gift_card_no;
$return_arr['info']['gift_card_pwd'] = $gift_card_pwd;

$award_type = $award_config[$the_award - 1][2];
if ($award_type == 2) {
	// 礼包类奖品
	$gift_path = $award_config[$the_award - 1][4];
	$return_arr['info']['gift_path'] = $gift_path;
}

// 封装一下，判断中奖的名次是否大于中奖等级个数
$award_level_count = count($award_config);
$log_award = ($the_award > $award_level_count) ? 0 : $the_award;

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'award_level' => $log_award,
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
$return_arr['info']['award_level_name'] = $award_config[$the_award - 1][0];
$return_arr['info']['award_name'] = $award_config[$the_award - 1][1];
$return_arr['info']['award_type'] = $award_config[$the_award - 1][2];
$return_arr['info']['package'] = $package;

echo json_encode($return_arr);
exit;