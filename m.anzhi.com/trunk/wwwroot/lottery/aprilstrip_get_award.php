<?php

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');

$return_arr = array(
    'status' => 0,
    'info' => array()
);

if (!$imsi) {
    $return_arr['status'] = 300;
    echo json_encode($return_arr);;
    exit;
}

// 可抽奖次数-1
$now_num = $redis -> setx('incr',$rkey_imsi_lottery_num, -1);
if ($now_num < 0) {
    // 没有抽奖机会，把缓存数量还原为0
    $now_num = $redis -> set($rkey_imsi_lottery_num, 0);
    $return_arr['status'] = 300;
    echo json_encode($return_arr);;
    exit;
}
// 写回库里
$where = array(
    'imsi' => $imsi
);
$data = array(
	'lottery_num' => $now_num,
	'time' => time(),
	'__user_table' => 'aprilstrip_lottery_num'
);
$model -> update($where,$data,'lottery/lottery');


//抽奖
load_helper('task');
$task_client = get_task_client();
$task_ret = $task_client->do('aprilstrip_lottery', $imsi);

$task_ret = json_decode($task_ret, true);

$the_award = $task_ret['award_level'];
$gift_card_no = empty($task_ret['gift_card_no'])? '' : $task_ret['gift_card_no'];
$gift_card_pwd = empty($task_ret['gift_card_pwd'])? '' : $task_ret['gift_card_pwd'];

$return_arr['status'] = 200;
$return_arr['info']['award_level'] = $the_award;
$return_arr['info']['left_num'] = $now_num;
$return_arr['info']['gift_card_no'] = $gift_card_no;
$return_arr['info']['gift_card_pwd'] = $gift_card_pwd;

// 封装一下，判断中奖的名次是否大于中奖等级个数
$award_level_count = count($award_config);
$log_award = ($the_award > $award_level_count) ? 0 : $the_award;

// 记日志
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'award' => $log_award,
    'time' => time(),
    'key' => 'aprilstrip_get_award'
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

echo json_encode($return_arr);
exit;