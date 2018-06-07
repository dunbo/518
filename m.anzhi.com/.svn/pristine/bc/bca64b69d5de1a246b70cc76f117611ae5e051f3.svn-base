<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];
$imsi_lottery = "friends_lottery:lottery_{$imsi}_{$aid}";

if($imsi && $imsi != '000000000000000' && strlen($imsi) == 15){
	$imsi_num = "friends_lottery:num_{$imsi}_{$aid}";
	$imsi_info = "friends_lottery:info_{$imsi}_{$aid}";
	$imsi_lottery = "friends_lottery:lottery_{$imsi}_{$aid}";
	$my_lottery = $redis -> setx('incr',$imsi_lottery,0);
	$my_num = $redis -> setx('incr',$imsi_num,0);
	if(!$my_num){
		$option = array(
			'where' => array(
				'imsi' => $imsi
			),
			'table' => 'friends_lottery_num'
		);
		$result = $model -> findOne($option,'lottery/lottery');
		if(!$result){
			$my_num = $redis -> setx('incr',$imsi_num,3);
			$data = array(
				'imsi' => $imsi,
				'num' => $my_num,
				'create_tm' => time(),
				'update_tm' => time(),
				'status' => 1,
				'__user_table' => 'friends_lottery_num'
			);
			$model -> insert($data,'lottery/lottery');
		}else{
			$redis -> setx('incr',$imsi_num,$result['num']);
		}
	}
	
	if(!$imsi || $imsi == '000000000000000' || strlen($imsi) != 15){
		$my_num = 0;
	}else{
		$my_num = $redis -> setx('incr',$imsi_num,0);
	}

	$my_info = $redis -> gethash($imsi_info);

	if(!$my_info){
		$info_option = array(
			'where' => array(
				'imsi' => $imsi,
				'status' => 0
			),
			'table' => 'friends_lottery_award'
		);
		$info_result = $model -> findOne($info_option,'lottery/lottery');
		if($info_result){
			$info_arr = array('imsi' => $imsi,'award_level' => $award_level,'create_tm' => time(),'update_tm' => time(),'status' => 0);
			$redis -> sethash($imsi_info,$info_arr);
		}
	}
	$my_info = $redis -> gethash($imsi_info);
	if($my_info['status'] == 0 && $my_info){
		$award_option = array(
			'where' => array(
				'config_type' => 'FRIENDS_AWARD',
				'status' => 1
			),
			'cache_time' => 86400,
			'table' => 'pu_config'
		);
		$award_result = $model -> findOne($award_option);
		$award_content = json_decode($award_result['configcontent'],true);
		$my_award = $award_content[$my_info['award_level']][0];
		$tplObj -> out['status'] = 2;
		$tplObj -> out['my_award'] = $my_award;
	}else{
		$tplObj -> out['status'] = 1;
	}
	$tplObj -> out['imsi_status'] = 1;
}else{
	$tplObj -> out['imsi_status'] = 2;
}
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'show_homepage'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

if(($my_lottery & 1) == 1){
	$lottery_1 = 1;
}else{
	$lottery_1 = 0;
}
if(($my_lottery & 2) == 2){
	$lottery_2 = 1;
}else{
	$lottery_2 = 0;
}

if(($my_lottery & 4) == 4){
	$lottery_3 = 1;
}else{
	$lottery_3 = 0;
}

$tplObj -> out['lottery_1'] = $lottery_1;
$tplObj -> out['lottery_2'] = $lottery_2;
$tplObj -> out['lottery_3'] = $lottery_3;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['my_num'] = $my_num;
$tplObj -> display("friends_lottery.html");

