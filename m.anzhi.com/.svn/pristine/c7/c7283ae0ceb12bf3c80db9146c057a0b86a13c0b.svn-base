<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$softid = $_GET['softid'];
$package = $_GET['package'];
$imsi = $_SESSION['USER_IMSI'];
$imsi_install_package = "general_lottery:{$imsi}_install_package_{$aid}";
$imsi_package = "general_lottery:{$imsi}_package_{$aid}";
$share_must = "general_lottery:share_must_{$aid}";
$imsi_num = "general_lottery:{$imsi}_num_{$aid}";
$the_today = "general_lottery:day_{$imsi}_{$aid}";
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'package' => $package,
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'install_soft'
);

permanentlog('activity_'.$aid.'.log', json_encode($log_data));

$share_must = "general_lottery:share_must_{$aid}";
$all_share = $redis -> gethash($share_must);
if($page_result['must_share'] == 1 && !$all_share[$imsi]){
	echo 200;
	return 200;
	exit;
}

$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);
$page_option = array(
	'where' => array(
		'ap_id' => $activity_result['activity_page_id'],
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);

$today = date("Ymd");
$today_num = $redis -> gethash($the_today);
if(!$today_num){
	$today_num[$today] = 0;
}
$lottery_num_limit = $page_result['lottery_num_limit'];
$old_package = $redis -> gethash($imsi_install_package);
if(!$old_package[$package]){
	$redis -> sethash($imsi_install_package,array($package => 1));
	if($page_result['get_lottery_type'] == 2 && ($today_num[$today] < $lottery_num_limit)){
		$now_num = $redis -> setx('incr',$imsi_num,1);
		$today_num_add = $today_num[$today] + 1;
		$today_num[$today] = $today_num_add;
		$redis -> sethash($the_today,$today_num);
		$where = array(
			'imsi' => $imsi,
			'aid' => $aid
		);
		$data = array(
			'lottery_num' => $now_num,
			'update_tm' => time(),
			'__user_table' => 'gm_lottery_num'
		);
		$num_result = $model -> update($where,$data,'lottery/lottery');
		if(!$num_result){
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'imsi' => $imsi,
				'aid' => $aid,
				'__user_table' => 'gm_lottery_num'
			);
			$num_results = $model -> insert($data,'lottery/lottery');
		}
	
		echo $now_num;
		return $now_num;
	}else if($page_result['get_lottery_type'] == 3 && ($today_num[$today] < $lottery_num_limit)){
		$old_download_package = $redis -> gethash($imsi_package,$package);
		if($old_download_package){
			$now_num = $redis -> setx('incr',$imsi_num,1);
			$today_num_add = $today_num[$today] + 1;
			$today_num[$today] = $today_num_add;
			$redis -> sethash($the_today,$today_num);
			$where = array(
				'imsi' => $imsi,
				'aid' => $aid
			);
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'__user_table' => 'gm_lottery_num'
			);
			$num_result = $model -> update($where,$data,'lottery/lottery');
			if(!$num_result){
				$data = array(
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'imsi' => $imsi,
					'aid' => $aid,
					'__user_table' => 'gm_lottery_num'
				);
				$num_results = $model -> insert($data,'lottery/lottery');
			}
		
			echo $now_num;
			return $now_num;
		}else{
			echo 200;
			return 200;
		}
	}else{
		echo 200;
		return 200;
	}
}else{
	echo 200;
	return 200;
}
