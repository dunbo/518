<?php
/*
** 我的奖品页
*/

include_once (dirname(realpath(__FILE__)).'/octoberflight_init_page.php');

$is_overdue = 0;
if ($now > $activity_end_time) {
	$is_overdue = 1;
}
$tplObj->out['is_overdue'] = $is_overdue;

$my_award_list = array();

$option = array(
	'where' => array(
		'imsi' => $imsi,
	),
	'order' => 'award asc, id desc',
	'table' => 'octoberflight_lottery_award'
);
$ret = $model->findAll($option, 'lottery/lottery');
if (!empty($ret)) {
	// 有中奖信息
	foreach ($ret as $row) {
		$my_award = array();
		$award_id = $row['id'];
		$award_level = $row['award'];
		$award_level_name = $award_config[$award_level-1][0];
		$award_name = $award_config[$award_level-1][1];
		// 判断一下奖品的属性
		$award_type = $award_config[$award_level-1][2];
		
		if ($award_type == 0 || $award_type == 1) {
			// 实物奖品
			$name = $row['name'];
			$telephone = $row['telephone'];
			
			$my_award['name'] = $name;
			$my_award['telephone'] = $telephone;
		} else if ($award_type == 2) {
			// 礼包奖品
			$gift_path = $award_config[$award_level-1][4];
			$my_award['package'] = $award_config[$award_level-1][5];
			$my_award['gift_path'] = $gift_path;
		}
		
		$my_award['award_id'] = $award_id;
		$my_award['award_level'] = $award_level;
		$my_award['award_level_name'] = $award_level_name;
		$my_award['award_name'] = $award_name;
		$my_award['award_type'] = $award_type;
		
		$my_award_list[] = $my_award;
	}
}

$tplObj->out['my_award_list'] = $my_award_list;
$tplObj->display('octoberflight_lottery_award.html');