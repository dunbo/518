<?php

include_once (dirname(realpath(__FILE__)).'/double11_init_page.php');

$unwritten = 0;

// 判断用户是否中了实物奖且未填信息，如果是，则跳转到填写信息页
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'double11_lottery_award',
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

$lottery_num = get_lottery_num();

if (!empty($_GET['actsid'])) {
	$actsid = $_GET['actsid'];
	// 端外分享带过来的数据
	// 判断是否已加过
	// 读出存在redis里的值
	$is_used = $redis->gethash($actsid, 'is_used');
	if (empty($is_used)) {
		// 更新为已用过，且将红包数加上
		$redis->sethash($actsid, 'is_used', 1);
		// 更新用户的分享状态
		$share_date = $redis->get($rkey_imsi_share_info);
		if ($share_date != $today) {
			// 今天没有分享过，判断带量过来的是否分享过
			$out_share_date = $redis->gethash($actsid, 'share_date');
			if ($out_share_date == $today) {
				// 更新用户分享日期
				$redis->set($rkey_imsi_share_info, $today, $r_cache_time);
			}
		}
		$redpacket_num = $redis->gethash($actsid, 'redpacket_num');
		// 判断用户今天通过抽红包获得的总数
		$open_get_info = $redis->setx('HINCRBY', $rkey_imsi_open_get_total_info, $today, $redpacket_num);
		if ($open_get_info > 5) {
			// 每天拆出的红包数超过5，不可以
			$decrease = 0 - $redpacket_num;
			// 还原
			$redis->setx('HINCRBY', $rkey_imsi_open_get_total_info, $today, $decrease);
			// 获得用户今天通过抽红包已用了多少
			$total_info = $redis->gethash($rkey_imsi_open_get_total_info, $today);
			$can_add = 5 - $total_info;
			if ($can_add < 0) {
				$can_add = 0;
			}
			// 更新用户今天通过抽红包已得的抽奖数
			if ($can_add > 0) {
				$redis->sethash($rkey_imsi_open_get_total_info, array($today => 5));
			}
		} else {
			$can_add = $redpacket_num;
		}
		if ($can_add > 0) {
			// 更新用户的可抽奖次数
			$lottery_num += $can_add;
			set_lottery_num($lottery_num);
		}
	}
}

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

$tplObj->out['lottery_num'] = $lottery_num;

$tplObj->display("double11_lottery.html");