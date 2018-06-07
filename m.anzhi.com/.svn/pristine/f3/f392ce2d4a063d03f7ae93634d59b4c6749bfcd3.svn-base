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

//防刷的 打开页面时间记下
$home_key_today = 'gen_brush:home:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
$redis->setnx($home_key_today,$now);
$redis->expire($home_key_today,86400*60);
		
$lottery_num = get_lottery_num();
$puzzle_num = get_puzzle_num();

$tplObj->out['lottery_num'] = $lottery_num;
$tplObj->out['puzzle_num'] = $puzzle_num;

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
	$award_id = $find['id'];
	$tplObj->out['award_level_name'] = $award_level_name;
	$tplObj->out['award_name'] = $award_name;
	$tplObj->out['award_id'] = $award_id;
}
$tplObj->out['unwritten'] = $unwritten;
//分享地址

$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];

$tplObj -> out['is_test'] = $configs['is_test'];

$tplObj-> out['share_text'] ="圣诞大狂欢，撒欢跨新年！ 现在来@安智市场 参加活动超多礼物等你拿，快和我一起去玩吧！";
if ($now > $activity_end_time) {
	$tplObj->display('lottery/christmas_pin_2016/end.html');
}
else{
	$tplObj->display('lottery/christmas_pin_2016/index.html');
}
exit;