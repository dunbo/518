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
$obj = $_GET['obj'];
if($obj == 3){
	$obj = 4;
}
$imsi = $_SESSION['USER_IMSI'];
$imsi_num = "friends_lottery:num_{$imsi}_{$aid}";
$imsi_info = "friends_lottery:info_{$imsi}_{$aid}";
$imsi_lottery = "friends_lottery:lottery_{$imsi}_{$aid}";
if($imsi && $imsi != '000000000000000' && strlen($imsi) == 15){
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	if($now_num >= 0){
		load_helper('task');
		$task_client = get_task_client();
		$the_award = $task_client->do('friends_lottery',$imsi);
		$the_award = json_decode($the_award,true);
		$the_award = $the_award[0];
		$num_where = array('imsi' => $imsi);
		$num_data = array(
			'num' => $now_num,
			'update_tm' => time(),
			'__user_table' => 'friends_lottery_num'
		);
		$model -> update($num_where,$num_data,'lottery/lottery');
		$redis -> setx('incr',$imsi_lottery,$obj);
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'award_level' => $the_award,
			'time' => time(),
			'key' => 'lottery'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		if($the_award == 0){
			$the_result = array(0,$now_num);
			echo json_encode($the_result);
			return json_encode($the_result);
		}else{
			$info_arr = array('imsi' => $imsi,'award_level' => $the_award,'create_tm' => time(),'update_tm' => time(),'status' => 0);
			$redis -> sethash($imsi_info,$info_arr);
			$content_option = array(
				'where' => array(
					'config_type' => 'FRIENDS_AWARD',
					'status' => 1
				),
				'cache_time' => 86400,
				'table' => 'pu_config'
			);
			$content_result = $model -> findOne($content_option);
			$config_content = json_decode($content_result['configcontent'],true);
			$my_award = $config_content[$the_award][0];
			$the_result = array($the_award,$now_num,$my_award);
			echo json_encode($the_result);
			return json_encode($the_result);
		}
	}else{
		$redis -> setx('incr',$imsi_num,1);
		$the_result = array(0,0);
		echo json_encode($the_result);
		return json_encode($the_result);
	}

}