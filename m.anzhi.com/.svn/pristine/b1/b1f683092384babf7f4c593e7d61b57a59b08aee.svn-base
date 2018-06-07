<?php

// 我的奖品页，活动结束后也可以看

include_once(dirname(realpath(__FILE__)) . '/init_page.php');

$is_overdue = 0;//是否过期
if ($now > $activity_end_time) {
	$is_overdue = 1;
}
$tplObj->out['is_overdue'] = $is_overdue;

$my_award_list = array();

$option = array(
	'where' => array(
		'imsi' => $imsi,
		'aid' => $aid,
	),
	'order' => 'award asc, id desc',
	'table' => 'imsi_lottery_award'
);
$ret = $model->findAll($option, 'lottery/lottery');
if (!empty($ret)) {
	// 有中奖信息
	foreach ($ret as $row) {
		$my_award = array();
		$award_id = $row['id'];
		$award_level = $row['award'];
		//$gift_card_pwd = $row['gift_card_pwd'];
		$award_name = get_prize_name($award_level);
		$award_level_name = $award_level_name_arr[$award_level];
		//$award_name = $award_config[$award_level-1][1];
		// 判断一下奖品的属性
		//$award_type = $award_config[$award_level-1][2];
		
		//if ($award_type == 0 || $award_type == 1) {
			// 实物奖品
			$name = $row['name'];
			$telephone = $row['telephone'];
			
			$my_award['name'] = $name;
			$my_award['telephone'] = $telephone;
			$my_award['address'] = $row['address'];
		/*} else if ($award_type == 2) {
			// 礼包奖品
			$gift_path = $award_config[$award_level-1][4];
			$my_award['package'] = $award_config[$award_level-1][5];
			$my_award['gift_path'] = $gift_path;
			$my_award['gift_card_pwd'] = $gift_card_pwd;
		}*/
		
		$my_award['award_id'] = $award_id;
		$my_award['award_level'] = $award_level;
		$my_award['award_level_name'] = $award_level_name;
		$my_award['award_name'] = $award_name;
		//$my_award['award_type'] = $award_type;
		
		$my_award_list[] = $my_award;
	}
}
$tplObj->out['my_award_list'] = $my_award_list;

$tplObj->display('lottery/long_holiday_2016/my_award.html');
exit;