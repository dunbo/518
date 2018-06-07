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

$tplObj->out['lottery_num'] = $lottery_num;

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
//分享地址和腾讯下载地址
//$share_url = $configs['activity_share_url'].'a_'.$aid.'.html';

//$tplObj -> out['share_url'] = $share_url;
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];

$tplObj -> out['is_test'] = $configs['is_test'];

$tplObj-> out['share_text'] ="拯救双十一吃土少年，厉害了Word安智市场！现在参加活动超多好奖等你拿，放纵购物双十一有奖有惊喜。";
if ($now > $activity_end_time) {
	$tplObj->display('lottery/double11_pin_2016/end.html');
}
else{
	$tplObj->display('lottery/double11_pin_2016/index.html');
}
exit;