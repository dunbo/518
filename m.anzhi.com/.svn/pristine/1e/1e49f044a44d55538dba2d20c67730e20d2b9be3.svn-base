<?php

require_once(dirname(realpath(__FILE__)) . '/init_page.php');


// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'show_homepage'
);
permanentlog($activity_log_file, json_encode($log_data));


$unwritten = 0;
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
	// 有未填信息，强制打开填写信息窗口
	$unwritten = 1;
	$award_level = $find['award'];
	$award_level_name = $award_level_name_arr[$award_level];
	$award_name = get_prize_name($award_level);
	$tplObj->out['award_level_name'] = $award_level_name;
	$tplObj->out['award_name'] = $award_name;
}
$tplObj->out['unwritten'] = $unwritten;

if ($now > $activity_end_time) {
	$tplObj->display('lottery/LittleElfInBag/end.html');
}
else{
	if ($grasp_num<3) {
		$tplObj->display('lottery/LittleElfInBag/game.html');
	}
	else{
		$tplObj->display('lottery/LittleElfInBag/game_end.html');
	}
}


exit;