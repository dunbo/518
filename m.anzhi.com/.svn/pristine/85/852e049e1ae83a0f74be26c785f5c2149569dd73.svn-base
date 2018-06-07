<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 167;
$config = load_config('lottery_cache/redis',"lottery");
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
	$imsi_info = $imsi.":info_{$active_id}";
}

if($imsi){
	$telphone = $_GET['telphone'];
	$name = $_GET['name'];
	$address = $_GET['address'];
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
			'table' => 'school_award'
		);
		$award_result = $model -> findOne($option,'lottery/lottery');
		if($award_result['award'] <= 3){
			$data = array(
				'telphone' => $telphone,
				'name' => $name,
				'address' => $address,
				'time' => $my_time,
				'status' => 1,
				'__user_table' => 'school_award'
			);
		}elseif($award_result['award'] >= 4 && $award_result['award'] <= 6){
			$data = array(
				'telphone' => $telphone,
				'time' => $my_time,
				'status' => 1,
				'__user_table' => 'school_award'
			);
		}
				
		$where = array(
			'imsi' => $imsi,
			'status' => 0
		);
		$options = array(
			'where' => array(
				'imsi' => $imsi,
				'status' => 0
			),
			'table' => 'school_award'
		);
		$results = $model -> findOne($options,'lottery/lottery');
		$result = $model -> update($where,$data,'lottery/lottery');

		if($result){
			$award_info = $redis -> gethash("award_{$imsi}:lottery_{$active_id}",$results['id']);
			
			if($award_info['award'] <= 3){
				$award_info['status'] = 1;
				$award_info['telphone'] = $telphone;
				$award_info['name'] = $name;
				$award_info['address'] = $address;
			}elseif($award_info['award'] >=4 && $award_info['award'] <= 6){
				$award_info['status'] = 1;
				$award_info['telphone'] = $telphone;
			}
			$redis -> sethash("award_{$imsi}:lottery_{$active_id}",array($results['id'] => $award_info));
		}
		$the_info_old = $redis -> gethash($imsi_info);
		$the_info = array($the_info_old[0],0);
		$redis -> sethash($imsi_info,$the_info,0,false,true);
		if($award_result['award'] <= 3){
			$log_data = array(
				'imsi' => $imsi,
				'device_id' => $_SESSION['DEVICEID'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
				'award' => $award_result['award'],
				'telphone' => $telphone,
				'name' => $name,
				'address' => $address,
				'activity_id' => $_GET['aid'],
				'time' => time(),
				'key' => 'lottery_telphone'
			);
		}elseif($award_result['award'] >= 4 && $award_result['award'] <= 6){
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
		}
		permanentlog('activity_'.$_GET['aid'].'.log', json_encode($log_data));
		
		$award_option = array(
			'where' => array(
				'imsi' => $imsi,
				'time' => $my_time,
			),
			'table' => 'school_award'
		);
		$award_result = $model -> findOne($award_option,'lottery/lottery');

		$award_level_option = array(
			'where' => array(
				'config_type' => 'SCHOOL_AWARD',
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
