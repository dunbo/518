<?php
/*
** 游戏开始接口
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
	'status' => 0
);

if (!$imsi_status) {
    $return_arr['status'] = 300; //没插sim卡
    echo json_encode($return_arr);
    exit;
}
// 尝试将用户游戏次数+1
$ret = incr_game_num();
if (!$ret) {
	$return_arr['status'] = 301; //游戏次数上限
	echo json_encode($return_arr);
	exit;
}
// 返回当前游戏次数
$now_num = get_game_num();
$return_arr = array(
	'status' => 200, //游戏开始
	'game_num' => $now_num
);
// 记录游戏次数日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'game'
);
permanentlog($activity_log_file, json_encode($log_data));
exit(json_encode($return_arr));