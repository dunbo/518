<?php

//1000，抽奖次数为0；300，抽奖次数为1；400,抽奖次数大于1；800,中奖了；
include_once (dirname(realpath(__FILE__)).'/../init.php');
$aid = $_GET['aid'];
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
	$imsi_num = "superman_lottery:num_{$imsi}_{$aid}";
	$imsi_info = "superman_lottery:info_{$imsi_{$aid}}";
}

if($imsi){
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	if($now_num >= 0){
		//抽奖
		load_helper('task');
		$task_client = get_task_client();
		//$the_award = $task_client->do('superman_lottery',$imsi);
		$the_award = json_decode($the_award,true);
		$the_award = array(8,$now_num);
		//$the_award = array(2,$now_num);
		$num_where = array('imsi' => $imsi);
		$num_data = array(
			'lottery_num' => $now_num,
			'time' => time(),
			'__user_table' => 'superman_lottery_num'
		);
		$num_result = $model -> update($num_where,$num_data,'lottery/lottery');
		$content_option = array(
			'where' => array(
				'config_type' => 'SUPERMAN_AWARD',
				'status' => 1
			),
			'cache_time' => 300,
			'table' => 'pu_config'
		);
		$content_result = $model -> findOne($content_option);
		$award_content = json_decode($content_result['configcontent'],true);
		$award_level = $award_content[$the_award[0]][0];
		$award_prize = $award_content[$the_award[0]][1];

		if($the_award[0] <= 3){
			$my_return = array($the_award[0],$now_num,$award_level,$award_prize);
		}elseif($the_award[0] >= 4 && $the_award[0] <= 7){
			$package_redis = "superman_lottery:package_{$aid}";
			$all_package = $redis -> gethash($package_redis);
			$package = $all_package[$the_award[0]];
			$my_return = array($the_award[0],$now_num,$award_level,$award_prize,$the_award[1],$package);
		}elseif($the_award[0] == 8){
			$my_return = array(8,$now_num);
		}
		if($the_award[0] >= 4 && $the_award[0] <= 7){
			$log_data = array(
				'imsi' => $imsi,
				'imei' => $_SESSION['USER_IMEI'],
				'device_id' => $_SESSION['DEVICEID'],
				'activity_id' => $aid,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
				'award' => $the_award[0],
				'time' => time(),
				'key' => 'award'
			);
		}else{
			$log_data = array(
				'imsi' => $imsi,
				'imei' => $_SESSION['USER_IMEI'],
				'device_id' => $_SESSION['DEVICEID'],
				'activity_id' => $aid,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
				'award' => $the_award[0],
				'time' => time(),
				'key' => 'lottery'
			);
		}
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		echo json_encode($my_return);
		return json_encode($my_return);
	}else{
		$now_num = $redis -> setx('incr',$imsi_num,1);
		echo 400;
		return 400;
	}
}