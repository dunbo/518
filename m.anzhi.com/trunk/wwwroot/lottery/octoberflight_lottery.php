<?php
/*
** 活动页面
*/

include_once (dirname(realpath(__FILE__)).'/octoberflight_init_page.php');

$unwritten = 0;

// 判断用户是否中了实物奖且未填信息，如果是，则跳转到填写信息页
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'octoberflight_lottery_award',
);
$find = $model->findOne($option, 'lottery/lottery');
if ($find) {
	// 有未填信息，强制打开填写信息窗口
	$unwritten = 1;
	$award_level = $find['award'];
	$award_level_name = $award_config[$award_level-1][0];
	$award_name = $award_config[$award_level-1][1];
	
	$tplObj->out['award_level_name'] = $award_level_name;
	$tplObj->out['award_name'] = $award_name;
}
$tplObj->out['unwritten'] = $unwritten;

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'show_lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

// 获得用户的可抽奖次数
$lottery_num = get_lottery_num();
$tplObj->out['lottery_num'] = $lottery_num;
$tplObj->display("octoberflight_lottery.html");
exit;