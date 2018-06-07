<?php

	include_once (dirname(realpath(__FILE__)).'/init.php');
	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();
	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
		session_id($_GET['sid']);
	}
	session_start();
	if($_SESSION['USER_IMSI']){
		$imsi = $_SESSION['USER_IMSI'];
	}else{
		$imsi = 9;
	}
	
	$softid = $_GET['softid'];
	$soft_option = array(
		'where' => array(
			'softid' => $softid,
			'status' => 1,
			'hide' => 1
		),
		'field' => 'package',
		'table' => 'sj_soft'
	);
	$package_result = $model -> findOne($soft_option);
	$package = $package_result['package'];
	$all_packages = $redis -> get('all_package');
	$all_package = json_decode($all_packages,true);
	foreach($all_package as $key => $val){
		$package_arr[] = $val['package'];
	}
	
	if(in_array($package,$package_arr)){
		$my_packages = $redis -> get($imsi); 
		$my_package = json_decode($my_packages,true);
		$my_package_arr = explode(',',$my_package['package']);
		$day = date('Ymd',time());
		if($my_packages){
			$downloaded_package = $my_package['package'].','.$package;
		}else{
			$downloaded_package = $package;
		}
		$redis_data = array(
			'imsi' => $imsi,
			'telphone' => $my_package['telphone'],
			'guess' => $my_package['guess'],
			'package' => $downloaded_package
		);
		$redis -> set($imsi,json_encode($redis_data),3888000);
		if($my_package['guess'][$day] < 6){
			if($my_packages){
				$my_package['guess'][$day] = $my_package['guess'][$day] + 1;
			}else{
				$my_package['guess'][$day] = 1;
			}
			
			$redis_data = array(
				'imsi' => $imsi,
				'telphone' => $my_package['telphone'],
				'guess' => $my_package['guess'],
				'package' => $downloaded_package
			);
			$redis -> set($imsi,json_encode($redis_data),3888000);
			$user_option = array(
				'where' => array(
					'imsi' => $imsi,
				),
				'table' => 'cup_user'
			);
			$user_result = $model -> findOne($user_option,'world_cup');
			
			$user_where = array(
				'imsi' => $imsi
			);
			$my_num = $user_result['guess_num'] + 1;
			$user_data = array(
				'guess_num' => $my_num,
				'__user_table' => 'cup_user'
			);
			$guess_result = $model -> update($user_where,$user_data,'world_cup');
			
			if($guess_result){
				echo $my_num;
				return $my_num;
			}
		}
		
	}