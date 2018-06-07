<?php

/*
** 抽奖页
*/

include_once (dirname(realpath(__FILE__)).'/init_page_expire.php');

// 是否为端外过来
if ($_GET['actsid']) {
	$actsid = $_GET['actsid'];
	// 判断用户是否为第一次进来
	$share_day = $redis->get($rkey_imsi_share);
	$lottery_num = $redis->get($rkey_imsi_lottery_num);
	if ($share_day != $today) {
		$share_day = $today;
		// 设置用户今天为已分享
		$redis->set($rkey_imsi_share, $share_day, $r_cache_time);
		// 给用户次数增加1
		$lottery_num++;
		set_lottery_num($lottery_num);
	}
	// 端外的分数带进来
	$score = $redis->gethash($actsid, 'score');
	if (empty($score)) {
		$score = 0;
	}
	$redis->set($rkey_imsi_score, $score, $r_cache_time);
}

// 是否有未填写的信息
$unwritten = 0;
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'aid' => $aid,
		'status' => 0,
	),
	'table' => 'brand_general_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');
if ($find) {
	// 有未填信息，强制打开填写信息窗口
	$unwritten = 1;
	$award_level = $find['level'];
	$award_level_name = $award_config[$award_level]['level_name'];
	$award_name = $award_config[$award_level]['name'];
	$tplObj->out['award_level_name'] = $award_level_name;
	$tplObj->out['award_name'] = $award_name;
}
$tplObj->out['unwritten'] = $unwritten;

$lottery_num = $redis->get($rkey_imsi_lottery_num);
$tplObj->out['lottery_num'] = $lottery_num;

$score = $redis->get($rkey_imsi_score);
$tplObj->out['score'] = $score;

// 根据分数，指定软件区间
if (array_key_exists($score, $score_arr)) {
	$cat_id = $score_arr[$score];
} else {
	$cat_id = $score_arr[0];
}
$tplObj->out['cat_id'] = $cat_id;

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
	'key' => 'show_lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display('lottery/guessappbattle/lottery.html');