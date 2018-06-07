<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 187;
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
$imsi = $_SESSION['USER_IMSI'];
$imei = $_SESSION['USER_IMEI'];
$user = $_POST['user'];

$all_user = $redis -> gethash("vacation_lottery_third:user_{$active_id}",md5($user));
$have_lottery_imsi = $redis -> gethash("vacation_lottery_third:user_imsi_{$active_id}",$imsi);
$have_lottery_imei = $redis -> gethash("vacation_lottery_third:user_imei_{$active_id}",$imei);
$have_lottery_user = $redis -> gethash("vacation_lottery_third:user_account_{$active_id}",$all_user);
if($have_lottery_imsi){
	echo 100;
	return 100;
	exit;
}

if($have_lottery_imei){
	echo 200;
	return 200;
	exit;
}

if($have_lottery_user){
	echo 300;
	return 300;
	exit;
}


if($all_user){
	load_helper('task');
	$task_client = get_task_client();
	$param = array($imsi,$user);
	$the_award_str = $task_client->do('vacation_lottery_third',json_encode($param));
	$the_award = json_decode($the_award_str,true);
	$redis -> sethash("vacation_lottery_third:user_account_{$active_id}",array($all_user => 1));
	$redis -> sethash("vacation_lottery_third:user_imsi_{$active_id}", array($imsi => 1));
	$redis -> sethash("vacation_lottery_third:user_imei_{$active_id}",array($imei => 1));
	if($the_award[0] < 6){
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_POST['sid'],
			'time' => time(),
			'award' => $the_award[0],
			'name' => '',
			'telphone' => '',
			'address' => '',
			'package' => '',
			'gift' => '',
			'key' => 'award'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		$content_option = array(
			"where" => array(
				'config_type' => 'VACATION_THIRD_AWARD',
				'status' => 1
			),
			'table' => 'pu_config'
		);
		$content_result = $model -> findOne($content_option);
		$award_content = json_decode($content_result['configcontent'],true);
		$my_award_content = $award_content[$the_award[0] - 1][1];
		$my_return = array($_POST['sid'],$the_award[0],$my_award_content);
		echo json_encode($my_return);
		return json_encode($my_return);
	}else if($the_award[0] == 6){
		$gift_arrs = $the_award[1];
		foreach($gift_arrs as $key => $val){
			$the_package = $key;
			$the_gift = $val;
		}
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_POST['sid'],
			'time' => time(),
			'award' => $the_award[0],
			'name' => '',
			'telphone' => '',
			'address' => '',
			'package' => $the_package,
			'gift' => $the_gift,
			'key' => 'award'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		$soft_info = $redis -> gethash("vacation_lottery_third:soft_{$active_id}");
		$soft_name = $soft_info[$the_package];
		$my_return = array($_POST['sid'],$the_award[0],$soft_name,$the_gift);
		echo json_encode($my_return);
		return json_encode($my_return);
	}else if($the_award[0] > 6){
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_POST['sid'],
			'user' => $user,
			'time' => time(),
			'status' => 1,
			'key' => 'lottery'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		echo 500;
		return 500;
	}
}else{
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_POST['sid'],
		'user' => $user,
		'time' => time(),
		'status' => 0,
		'key' => 'lottery'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	echo 400;
	return 400;
}

