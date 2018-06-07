<?php

require_once(dirname(realpath(__FILE__)) . '/init_page_expire.php');


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


$lottery_num = get_lottery_num();
$puzzle_num = get_puzzle_num();

$tplObj->out['lottery_num'] = $lottery_num;
$tplObj->out['puzzle_num'] = $puzzle_num;

$unwritten = 0;
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0,
	),
	'table' => 'rio_olympic_lottery_award',
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
if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
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
}
$tplObj -> out['tencent_url'] = $tencent_url;
$tplObj -> out['share_url'] = $share_url;
$tplObj-> out['share_text'] ="里约奥运会被这个有毒的小游戏玩儿坏了！来安智市场跟我一起玩游戏中大奖~";
$tplObj->display('lottery/RioOlympicGames/index.html');
exit;