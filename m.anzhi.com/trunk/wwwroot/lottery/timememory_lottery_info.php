<?php

require_once(dirname(realpath(__FILE__)) . '/timememory_init_page.php');

$now_num = $redis->get($rkey_imsi_lottery_num);
$tplObj->out['now_num'] = $now_num;

// 查找用户中奖且未填相关信息的内容
// 如果是从抽奖进来，会展示礼包，否则不会展示礼包
$where = array(
	'imsi' => $imsi,
);

$option = array(
	'where' => $where,
	'order' => 'time desc',
	'table' => 'timememory_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');

if (empty($find)) {
	header('location:/lottery/timememory_lottery.php?sid='.$_GET['sid']);
	exit;
}

$award_level = $find['award'];
$award_type = $award_config[$award_level-1][2];
if ($award_type != 2 && $find['status'] != 0) {
	// 如果不是礼包，且状态不为0，则跳转回去
	header('location:/lottery/timememory_lottery.php?sid='.$_GET['sid']);
	exit;
}

$ret = array();
$award_level = $find['award'];
$ret['award_level'] = $award_level;
$ret['award_level_name'] = $award_config[$award_level-1][0];
$ret['award_name'] = $award_config[$award_level-1][1];
$ret['award_type'] = $award_config[$award_level-1][2];
$ret['package'] = $find['package'];
$ret['gift_card_no'] = $find['gift_card_no'];
$ret['gift_card_pwd'] = $find['gift_card_pwd'];

$tplObj->out['my_award'] = $ret;
$tplObj->display("timememory_lottery_info.html");