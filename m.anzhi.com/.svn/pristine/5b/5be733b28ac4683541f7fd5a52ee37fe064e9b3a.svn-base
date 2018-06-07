<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 186;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_POST['sid'] && eregi('[0-9a-zA-z]', $_POST['sid']) && strlen($_POST['sid']) == 32){
	session_id($_POST['sid']);
}

session_start();
$c = mt_rand(10000000000,99999999999);
$imsi = 4600 . $c;
$imsi_num = "vacation_lottery_second:num_{$imsi}_{$active_id}";
$my_num = $redis -> setx('incr',$imsi_num,3);
if($my_num && $imsi){
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	load_helper('task');
	$task_client = get_task_client();
	$the_award_str = $task_client->do('vacation_lottery_second',$imsi);
	$the_award = json_decode($the_award_str,true);
	$num_where = array('imsi' => $imsi);
	$num_data = array(
		'lottery_num' => $now_num,
		'time' => time(),
		'__user_table' => 'vacation_lottery_num'
	);
	$num_result = $model -> update($num_where,$num_data,'lottery/lottery');
	if($the_award[0] <= 8){
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'award_level' => $the_award[0],
			'name' => '',
			'telphone' => '',
			'address' => '',
			'package' => '',
			'gift' => '',
			'time' => time(),
			'key' => 'award'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		$content_option = array(
			"where" => array(
				'config_type' => 'VACATION_LOTTERY_SECOND',
				'status' => 1
			),
			'table' => 'pu_config'
		);
		$content_result = $model -> findOne($content_option);
		$award_content = json_decode($content_result['configcontent'],true);
		$my_award_content = $award_content[$the_award[0] - 1][1];
		$my_return = array($_GET['sid'],$now_num,$the_award[0],$my_award_content);
		echo json_encode($my_return);
		return json_encode($my_return);
	}else{
		$gift_arr = $the_award[1];
		foreach($gift_arr as $key => $val){
			$the_package = $key;
			$the_gift = $val;
		}
	
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'award_level' => $the_award[0],
			'name' => '',
			'telphone' => '',
			'address' => '',
			'package' => $the_package,
			'gift' => $the_gift,
			'time' => time(),
			'key' => 'award'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		
		$soft_info = $redis -> gethash("vacation_lottery_second:soft_{$active_id}");
		$soft_name = $soft_info[$the_package];
		$my_return = array($_GET['sid'],$now_num,$the_award[0],$soft_name,$the_gift);
		echo json_encode($my_return);
		return json_encode($my_return);
	}
}else{
	echo 300;
	return 300;
}







