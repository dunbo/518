<?php

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init_page.php');

// 记日志
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'aprilstrip_mirror'
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
$tplObj -> out['my_num'] = $my_num;
$tplObj->display("aprilstrip_mirror.html");
exit;




