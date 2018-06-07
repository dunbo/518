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
$package = $_GET['package'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
	$imsi_num = "schoolseason_lottery:num_{$imsi}_{$aid}";
	$imsi_info = "schoolseason_lottery:info_{$imsi}_{$aid}}";
}

if($imsi && $imsi != '000000000000000'){
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	if($now_num >= 0){
		//抽奖
		load_helper('task');
		$task_client = get_task_client();
		$the_award = $task_client->do('schoolseason_lottery',$imsi);
		$the_award = json_decode($the_award,true);
		$num_where = array('imsi' => $imsi);
		$num_data = array(
			'num' => $now_num,
			'update_tm' => time(),
			'__user_table' => 'schoolseason_lottery_num'
		);
		$num_result = $model -> update($num_where,$num_data,'lottery/lottery');
		$content_option = array(
			'where' => array(
				'config_type' => 'SCHOOLSEASON_AWARD',
				'status' => 1
			),
			'cache_time' => 300,
			'table' => 'pu_config'
		);
		$content_result = $model -> findOne($content_option);
		$award_content = json_decode($content_result['configcontent'],true);
		$award_level = $award_content[$the_award[0]][0];
		$award_prize = $award_content[$the_award[0]][1];

		if($the_award[0] <= 4){
			$my_return = array($the_award[0],$now_num,$award_level,$award_prize);
		}elseif($the_award[0] == 5){
			$my_return = array($the_award[0],$now_num,$the_award[1]);
		}elseif($the_award[0] == 6){
			$my_return = array(6,$now_num);
		}
		if($the_award[0] == 5){
			$log_data = array(
				'imsi' => $imsi,
				'imei' => $_SESSION['USER_IMEI'],
				'device_id' => $_SESSION['DEVICEID'],
				'activity_id' => $aid,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
				'award' => $the_award[0],
				'gift_num' => $the_award[1],
				'time' => time(),
				'key' => 'award'
			);
			permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		}
		
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
		
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		echo json_encode($my_return);
		return json_encode($my_return);
	}else{
		$now_num = $redis -> setx('incr',$imsi_num,1);
		echo 0;
		return 0;
	}
}
