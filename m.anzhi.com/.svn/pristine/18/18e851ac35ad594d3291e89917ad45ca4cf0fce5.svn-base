<?php
	include_once (dirname(realpath(__FILE__)).'/superbowl_init.php');
	$now = date("Ymd");
	$rkey_today_award_left = "superbowl:{$active_id}:{$now}:award_left";

	$today_award_left = $redis->gethash($rkey_today_award_left);
	$prize = getUserAward();
	//var_dump($prize);
	$page_result = get_page_result();
//	var_dump($page_result);
	//软件分类（获奖或非获奖）
	$award_soft_category = award_soft_category();

	//获取当天投票软件
	$vote_app = getUserVoteApp();
	//抽奖次数
	$lottery_num = getLotteryNum();

	// 记日志
	$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'key' => 'show_homepage'
	);
	permanentlog($activity_log_file, json_encode($log_data));
	//未填收货信息的奖品
	$invalid_award = getInvalidAward();
//var_dump($invalid_award);
	if($invalid_award){
		$invalid_award['prizename'] = $activity_config['award_config'][$invalid_award['award']-1][0].'&nbsp;&nbsp;'.$activity_config['award_config'][$invalid_award['award']-1][1];
		$tplObj->out['invalid_award'] = $invalid_award;
	}

	$tplObj->out['lottery_num'] = $lottery_num;
	$tplObj->out['vote_app'] = $vote_app;
	$tplObj->out['award_soft_id'] = $award_soft_category[0]['id'];
	$tplObj->out['n_award_soft_id'] = $award_soft_category[1]['id'];
	$tplObj->out['page_result'] = $page_result;
	$tplObj->out['prize'] = $prize;
	$tplObj -> out['imgurl'] = getImageHost();
	$tplObj->display("superbowl/index.html");