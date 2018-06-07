<?php
//超级碗压测
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




//抽奖操作
$imsi = '400000000000000';
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

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => '200000000000000',
    'activity_id' => $active_id,
    'ip' =>'',
    'sid' => $_GET['sid'],
    'award_level' => $log_award,
    'time' => time(),
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
echo json_encode($return_arr);

// 记日志
$log_data = array(
    'imsi' =>  $imsi,
    'device_id' => '200000000000000',
    'activity_id' => $active_id,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_POST['sid'],
    'award_level' => $award,
    'telphone' => $telephone,
    'name' => $name,
    'time' => time(),
    'key' => 'award'
);
permanentlog($activity_log_file, json_encode($log_data));
exit;