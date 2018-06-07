<?php
/*
** 抽奖页
*/
include_once (dirname(realpath(__FILE__)).'/aprilstrip_init_page.php');

// 取出礼包类奖品等级
$gift_award_level_arr = array();
foreach ($award_config as $key => $ward_row) {
	if ($award_row[2] == 2) {
		$gift_award_level_arr[] = $key + 1;
	}
}

// 检查用户是否中奖了（非礼包类的奖）且未填相关信息
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'aprilstrip_lottery_award',
);
$list = $model->findOne($option, 'lottery/lottery');
if (!empty($list)) {
	// 中了非礼包类的奖品，跳转到填写信息页
	header("location:/lottery/aprilstrip_lottery_info.php?sid={$_GET['sid']}");
	exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'aprilstrip_lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

// 获得用户剩余抽奖次数
$my_num = $redis -> get($rkey_imsi_lottery_num);
if ($my_num === null) {
	$option = array(
		'where' => array(
			'imsi' => $imsi,
		),
		'field' => 'lottery_num',
		'table' => 'aprilstrip_lottery_num',
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if (!empty($find['lottery_num'])) {
		$my_num = $find['lottery_num'];
	} else {
		$my_num = 0;
	}
	// 写redis
	$redis->set($rkey_imsi_lottery_num, $my_num, $r_cache_time);
}
$tplObj->out['my_num'] = $my_num;

$tplObj->display("aprilstrip_lottery.html");
exit;