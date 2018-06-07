<?php

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init_page.php');

$today = date("Y-m-d");

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

// 判断用户今天是否分享过
$ever_share = 0;
$last_share_day = $redis->get($rkey_imsi_last_share_day);
if ($last_share_day === null) {
	// 之前没有分享过
} else {
	// 分享过，判断分享时间是否为今天
	if ($last_share_day == $today) {
		// 今天分享过
		$ever_share = 1;
	}
}

$tplObj->out['ever_share'] = $ever_share;
$tplObj->display("aprilstrip_card.html");
