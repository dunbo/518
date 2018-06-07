<?php
/*
** 活动抽奖页
*/
require_once(dirname(realpath(__FILE__)).'/init_page.php');

brush_second_do($aid, 1); //同一秒操作进黑名单函数

$unwritten = 0; //中奖用户是否有未填信息 0：没有 1：有
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0,
		'aid' => $aid,
	),
	'table' => 'imsi_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');
if ($find) {
	// 中奖用户有未填信息，强制打开填写信息窗口
	$unwritten = 1;
	$award_level = $find['award'];
	$award_level_name = $award_level_name_arr[$award_level];
	$award_name = get_prize_name($award_level);
	$award_id = $find['id'];
	$tplObj->out['award_level_name'] = $award_level_name;
	$tplObj->out['award_name'] = $award_name;
	$tplObj->out['award_id'] = $award_id;
}
$tplObj->out['unwritten'] = $unwritten;

$tplObj->display('lottery/forfather_201706/index.html');