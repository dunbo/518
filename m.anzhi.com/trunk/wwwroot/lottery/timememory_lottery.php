<?php

// 抽奖页

require_once(dirname(realpath(__FILE__)) . '/timememory_init_page.php');

// 如果用户有未填的中奖信息，则跳转到填写信息页
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0,
	),
	'table' => 'timememory_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');
if ($find) {
	header('location:/lottery/timememory_lottery_info.php?sid='.$_GET['sid']);
	exit;
}

$now_num = $redis->get($rkey_imsi_lottery_num);

$tplObj->out['now_num'] = $now_num;
$tplObj->display('timememory_lottery.html');