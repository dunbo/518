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
	$tplObj->out['award_level_name'] = $award_level_name;
	$tplObj->out['award_name'] = $award_name;
}
$tplObj->out['unwritten'] = $unwritten;
//分享地址和腾讯下载地址
$share_url = $configs['activity_share_url'].'a_'.$aid.'.html';

/*if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
{
	$share_url = 'http://m.test.anzhi.com/a_'.$aid.'.html';
	$tencent_url = 'http://m.test.anzhi.com/download.php?package=com.tencent.news';
}
elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
{
	$share_url = 'http://9.m.anzhi.com/a_'.$aid.'.html';
}
else 
{
	$share_url = 'http://fx.anzhi.com/a_'.$aid.'.html';
	$tencent_url = 'http://m.anzhi.com/download.php?package=com.tencent.news';
}*/
$tplObj -> out['tencent_url'] = $tencent_url;
$tplObj -> out['share_url'] = $share_url;
$tplObj-> out['share_text'] ="国庆福利就在@安智市场 这个有奖又魔性的活动好玩到上瘾，让假期不再无聊的秘籍→";
if ($now > $activity_end_time) {
	$tplObj->display('lottery/long_holiday_2016/end.html');
}
else{
	$tplObj->display('lottery/long_holiday_2016/index.html');
}
exit;