<?php

	include_once (dirname(realpath(__FILE__)).'/init.php');
	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();
	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid'])){
		session_id($_GET['sid']);
	}
	session_start();
	//if($_SESSION['USER_IMSI']){
		$imsi = $_SESSION['USER_IMSI'];
	//}else{
	//	$imsi = 10;
	//}
	$option = array(
		'where' => array(
			'imsi' => $imsi
		),
		'table' => 'cup_user'
	);
	$user_result = $model -> findOne($option,'world_cup');
	
	$aid = $_GET['aid'];
	
	$my_guess = $_GET['my_guess'];
	$match_id = $_GET['match_id'];
	$option = array(
		'where' => array(
			'id' => $match_id
		),
		'table' => 'cup_match'
	);
	$result = $model -> findOne($option,'world_cup');
	if($result['begintime'] < time() || $result['result']){  //比赛已开始，不可竞猜
		echo 200;
		return 200;
		exit;
	}
	
	$have_option = array(
		'where' => array(
			'match_id' => $match_id,
			'imsi' => $imsi
		),
		'table' => 'cup_guess'
	);
	$have_result = $model -> findOne($have_option,'world_cup');
	
	if($have_result['guess_content']){
		echo 300;
		return 300;
		exit;
	}
	
	if($user_result['guess_num'] == 0){
		echo 400;
		return 400;
		exit;
	}
	
	
	$match_data = array(
		'userid' => $user_result['userid'],
		'imsi' => $imsi,
		'mobile' => $user_result['mobile'],
		'match_id' => $match_id,
		'guess_content' => $my_guess,
		'create_tm' => time(),
		'update_tm' => time(),
		'guess_result' => 0,
		'__user_table' => 'cup_guess'
	);
	$match_result = $model -> insert($match_data,'world_cup');
	
	if($match_result){
		$now_guess = $user_result['guess_num'] - 1;
		$where = array(
			'imsi' => $imsi
		);
		$user_data = array(
			'guess_num' => $now_guess,
			'update_tm' => time(),
			'__user_table' => 'cup_user'
		);
		$user_update_result = $model -> update($where,$user_data,'world_cup');
		
		if($user_update_result){
			echo $now_guess;
			return $now_guess;
		}
	}
	