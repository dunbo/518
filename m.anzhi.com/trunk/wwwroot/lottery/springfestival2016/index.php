<?php

require_once(dirname(realpath(__FILE__)) . '/init_page_expire.php');

$actsid = $_GET['actsid'];

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
	'actsid' => $_GET['actsid'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'index'
);
permanentlog($activity_log_file, json_encode($log_data));

$lottery_num = get_lottery_num();

$share_day = $redis->get($rkey_imsi_share);
$ever_shared = 0;
if (!empty($share_day)) {
	// 分享过
	$ever_shared = 1;
}

if (!empty($actsid)) {
	// 端外数据带进来
	$bring_share_day = $redis->gethash($actsid, 'share_day');
	if (!empty($bring_share_day)) {
		$bring_share_time = strtotime($bring_share_day);
	}
	$bring_share_time = empty($bring_share_time) ? 0 : $bring_share_time;
	if (!empty($share_day)) {
		$share_time = strtotime($share_day);
	}
	$share_time = empty($share_time) ? 0 : $share_time;
	if ($bring_share_time > $share_time) {
		// 分享日期更新为最新的日期
		$redis->set($rkey_imsi_share, $bring_share_day);
		$ever_shared = 1;
	}
	if ($share_day != $today && $bring_share_day == $today) {
		// 今天没有分享过
		$lottery_num += 1;
		set_lottery_num($lottery_num);
	}
}
$tplObj->out['ever_shared'] = $ever_shared;
$tplObj->out['lottery_num'] = $lottery_num;

$unwritten = 0;
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0,
	),
	'table' => 'springfestival2016_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');
if ($find) {
	// 有未填信息，强制打开填写信息窗口
	$unwritten = 1;
	$award_level = $find['award'];
	$award_level_name = $award_config[$award_level-1][0];
	$award_name = $award_config[$award_level-1][1];
	$tplObj->out['award_level_name'] = $award_level_name;
	$tplObj->out['award_name'] = $award_name;
}
$tplObj->out['unwritten'] = $unwritten;

$tplObj->display('lottery/springfestival2016/index.html');
exit;