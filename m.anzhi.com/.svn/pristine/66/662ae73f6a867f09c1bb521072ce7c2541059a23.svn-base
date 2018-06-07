<?php
/*
** 我的奖品页（结束后）
*/
include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');

// 如果没有imsi，则跳转到魔镜页
if (!$imsi_status) {
	$tplObj->display("aprilstrip_mirror.html");
	exit;
}

$option = array(
	'where' => array(
		'imsi' => $imsi
	),
	'table' => 'aprilstrip_lottery_award',
);

$list = $model->findAll($option, 'lottery/lottery');

foreach ($list as $key => $row) {
	$award_level = $row['award'];
	$award_level_name = $award_config[$award_level - 1][0];
	$award_name = $award_config[$award_level - 1][1];
	$award_hint = $award_config[$award_level - 1][3];//列表里的奖品提示
	
	$list[$key]['award_level_name'] = $award_level_name;
	$list[$key]['award_name'] = $award_name;
	$list[$key]['award_hint'] = $award_hint;
}

$tplObj->out['my_award_list'] = $list;
$tplObj->display("aprilstrip_lottery_award.html");