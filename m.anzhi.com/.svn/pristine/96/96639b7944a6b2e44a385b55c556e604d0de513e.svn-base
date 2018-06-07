<?php

// 我的奖品页
require_once(dirname(realpath(__FILE__)) . '/timememory_init_page.php');

// 查找我的奖品
$option = array(
	'where' => array(
		'imsi' => $imsi,
	),
	'order' => 'award asc, time asc',
	'table' => 'timememory_lottery_award',
);
$my_award_list = $model->findAll($option, 'lottery/lottery');

if ($my_award_list) {
	foreach ($my_award_list as $key => $row) {
		$award_level = $row['award'];
		$my_award_list[$key]['award_level_name'] = $award_config[$award_level - 1][0];
		$my_award_list[$key]['award_name'] = $award_config[$award_level - 1][1];
		$my_award_list[$key]['award_type'] = $award_config[$award_level - 1][2];
		$my_award_list[$key]['hint'] = $award_config[$award_level - 1][4];
	}
}

$tplObj->out['my_award_list'] = $my_award_list;
$tplObj->display('timememory_lottery_award.html');
exit;