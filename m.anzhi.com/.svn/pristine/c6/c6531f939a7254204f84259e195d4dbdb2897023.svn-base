<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}
session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
	$imsi_info = $imsi.":info";
}
if($imsi){
	$telphone = $_GET['telphone'];
	$my_time = time();
	if(!preg_match("/^1[34578][0-9]{9}$/",$telphone) || strlen($telphone) != 11){
		echo 500;
		return 500;
	}else{
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'status' => 0
			),
			'table' => 'nd_award'
		);
		$award_result = $model -> findOne($option,'lottery/lottery');
		$data = array(
			'telphone' => $telphone,
			'time' => $my_time,
			'status' => 1,
			'__user_table' => 'nd_award'
		);
		$where = array(
			'imsi' => $imsi,
			'status' => 0
		);
		$result = $model -> update($where,$data,'lottery/lottery');
		$the_info_old = $redis -> gethash($imsi_info);
		$the_info = array($the_info_old[0],0);
		$redis -> sethash($imsi_info,$the_info,0,false,true);
		$log_data = array(
			'imsi' => $imsi,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'award' => $award_result['award'],
			'telphone' => $telphone,
			'activity_id' => $_GET['aid'],
			'time' => time(),
			'key' => 'lottery_telphone'
		);
		permanentlog('activity_'.$_GET['aid'].'.log', json_encode($log_data));
		
		$award_option = array(
			'where' => array(
				'imsi' => $imsi,
				'time' => $my_time,
			),
			'table' => 'nd_award'
		);
		$award_result = $model -> findOne($award_option,'lottery/lottery');

		$award_level_option = array(
			'where' => array(
				'config_type' => 'ND_AWARD',
				'status' => 1
			),
			'table' => 'pu_config'
		);
		$award_level_result = $model -> findOne($award_level_option);
		$award_level_arr = json_decode($award_level_result['configcontent'],true);
		$award_level = $award_level_arr[$award_result['award']][0];
		$prize = $award_level_arr[$award_result['award']][1];
		
		if($result){
			$data = array(200,$telphone,$award_level,$prize);
			echo json_encode($data);
			return json_encode($data);
		}
	}
}
