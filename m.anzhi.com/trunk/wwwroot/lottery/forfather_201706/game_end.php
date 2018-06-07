<?php
/*
** 游戏结束接口
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
	'status' => 0,
	'lottery_num' => 0
);

if (!$imsi_status) {
    $return_arr['status'] = 300;
    echo json_encode($return_arr);
    exit;
}
// 用户当前的可抽奖次数
$now_num = get_lottery_num();
$bitCount = $_GET['bitCount'];
if ($bitCount > 4) {
	// 灭蚊成功
	$now_num += 1;
	set_lottery_num($now_num);
	$return_arr = array(
		'status' => 201,
		'lottery_num' => $now_num
	);
} else {
	// 灭蚊失败
	$return_arr = array(
		'status' => 202,
		'lottery_num' => $now_num
	);
}
exit(json_encode($return_arr));