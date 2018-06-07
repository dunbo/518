<?php
/*
** 我的奖品页，活动结束也可查看
*/
require_once(dirname(realpath(__FILE__)).'/init_page.php');

$is_overdue = 0; //活动是否过期 0：没过期 1：过期
if ($now > $a_end_time) {
	$is_overdue = 1;
}
$tplObj->out['is_overdue'] = $is_overdue;

$my_award_list = array();
$option = array(
	'where' => array(
		'aid' => $aid,
		'imsi' => $imsi,
        'status' => 1
	),
	'order' => 'award asc, id desc',
	'table' => 'imsi_lottery_award'
);
$result = $model->findAll($option, 'lottery/lottery');
if (!empty($result)) {
    // 奖品不为空
    foreach ($result as $row) {
    	$my_award = array();
    	$award_level = $row['award'];
    	$award_name = get_prize_name($award_level);
    	$award_level_name = $award_level_name_arr[$award_level];
    	$my_award['award_id'] = $row['id']; //中奖序号
    	$my_award['award_level'] = $award_level; //中奖等级 1,2,3...
    	$my_award['award_name'] = $award_name; //奖品名称
    	$my_award['award_level_name'] = $award_lever_name; //奖品等级 一等奖,二等奖...
    	$my_award['name'] = $row['name']; //中奖者姓名
    	$my_award['telephone'] = $row['telephone']; //中奖者电话
    	$my_award['address'] = $row['address']; //中奖者地址

    	$my_award_list[] = $my_award;
    }
} else {
    // 跳转到没中奖页面
    $tplObj->display('lottery/forfather_201706/no_award.html');
    exit;
}

$tplObj->out['my_award_list'] = $my_award_list;
$tplObj->display('lottery/forfather_201706/my_award.html');