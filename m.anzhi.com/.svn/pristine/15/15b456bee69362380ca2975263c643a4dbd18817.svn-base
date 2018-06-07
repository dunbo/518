<?php

include_once(dirname(realpath(__FILE__)) . '/christmas2015_init_page_expire.php');

// 是否从端外推广带进来的
$actsid = $_GET['actsid'];
$lottery_num = get_lottery_num();

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
	'actsid' => $_GET['actsid'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'show_lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

if (!empty($actsid)) {
	$info = $redis->gethash($actsid);
	if (!empty($info)) {
		// 将爱点亮和分享点亮置1
		$ignite_love = $redis->get($rkey_imsi_ignite_love);
		$ignite_share = $redis->get($rkey_imsi_ignite_share);
		
		$bring_love_day = $redis->gethash($actsid, 'ignite_love');
		$bring_share_day = $redis->gethash($actsid, 'ignite_share');
		
		$flag = false;
		if ($ignite_love != $today && $bring_love_day == $today) {
			$redis->set($rkey_imsi_ignite_love, $today, $r_cache_time);
			$ignite_love = $today;
			$flag = true;
		}
		if ($ignite_share != $today && $bring_share_day == $today) {
			$redis->set($rkey_imsi_ignite_share, $today, $r_cache_time);
			$ignite_share = $today;
			$flag = true;
		}
		
		if ($ignite_love == $today || $ignite_share == $today) {
			if ($flag) {
				// 可抽奖次数+1
				$lottery_num++;
				set_lottery_num($lottery_num);
			}
		} else {
			// 非今天用的端外，今天才安装，回跳回首页
			header("location:/lottery/christmas2015_index.php?sid={$sid}");
			exit;
		}
	}
}

// 查找用户是否有中奖且未填信息的记录
$unwritten = 0;
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0,
	),
	'table' => 'christmas2015_lottery_award',
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
$tplObj->out['lottery_num'] = $lottery_num;

$tplObj->display('lottery/christmas2015/christmas2015_lottery.html');
exit;