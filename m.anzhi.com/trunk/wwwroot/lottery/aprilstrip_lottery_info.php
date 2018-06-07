<?php
/*
** 中奖填信息页
*/
include_once (dirname(realpath(__FILE__)).'/aprilstrip_init_page.php');

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

// 查此用户中了什么奖（非礼包类的奖）
// 取出礼包类奖品等级
$gift_award_level_arr = array();
foreach ($award_config as $key => $ward_row) {
	if ($award_row[2] == 2) {
		$gift_award_level_arr[] = $key + 1;
	}
}

$first = $_GET['first'];
$award_level = 0;
$award_row = array();
if (!empty($first)) {
	// 检查用户是否中奖了
	$option = array(
		'where' => array(
			'imsi' => $imsi,
			'telephone' => ''
		),
		'order' => 'id desc',
		'table' => 'aprilstrip_lottery_award',
	);
	$list = $model->findOne($option, 'lottery/lottery');
	if (empty($list)) {
		// 没有中奖
		exit;
	} else {
		$award_level = $list['award'];
		$award_row = $list;
	}
} else {
	// 非第一次显示中奖信息，只显示非礼包类奖品
	$option = array(
		'where' => array(
			'imsi' => $imsi,
			'telephone' => ''
		),
		'order' => 'id desc',
		'table' => 'aprilstrip_lottery_award',
	);
	$list = $model->findAll($option, 'lottery/lottery');
	if (empty($list)) {
		// 没有中奖
		//exit;
	}
	foreach ($list as $row) {
		$award = $row['award'];
		if (!in_array($award, $gift_award_level_arr)) {
			$award_level = $award;
			$award_row = $row;
			break;
		}
	}
}

if (empty($award_level)) {
	//exit;
}

// 判断中奖的内容是否为实物
$award_type = $award_config[$award_level-1][2];

// 几等奖
$award = $award_config[$award_level-1][0];
// 奖品内容
$award_name = $award_config[$award_level-1][1];
// 奖品提示（中奖页的提示）
$award_hint = $award_config[$award_level-1][4];

$tplObj->out['award_type'] = $award_type;
$tplObj->out['award_level_name'] = $award;
$tplObj->out['award_name'] = $award_name;
$tplObj->out['award_hint'] = $award_hint;

if ($award_type == 2) {
	// 包名、礼包卡号、礼包卡密
	$package = $award_row['package'];
	$tplObj->out['package'] = $package;
	$tplObj->out['gift_card_no'] = $award_row['gift_card_no'];
	$tplObj->out['gift_card_pwd'] = $award_row['gift_card_pwd'];
}

$tplObj->display("aprilstrip_lottery_info.html");
exit;