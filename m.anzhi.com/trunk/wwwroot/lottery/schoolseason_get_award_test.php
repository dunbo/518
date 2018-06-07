<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
ini_set('memory_limit',-1);
$aid = 294;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

$b = array("460042359383380","460086504303738","460011705796755","460066600163811","460073760208920","460092178990235","460034351743375","460086530305114","460024872225457","460071144031826","460030233084908","460088584916656","460013804672458","460013855218561","460039294304493","460073258131998","460070875446228","460059990915940","460015370965804","460094962560934","460092343491874","460090393188926","460086690816967","460053412941116","460039791800077","460059800300104","460086622319091","460087246883404","460078192938724","460078140494581","460019626873075","460058071021740","460034240615977","460025858003948","460099604764641","460043462001704","460073659513597","460043237969651","460046116390344","460070159321734","460055352379735","460097129613822","460016603109971","460013098810431","460040402766596","460094160059713","460082125214678","460066191885583","460065071217878","460059791540009");

//写次数

foreach($b as $key => $val){
	$imsi_num = "schoolseason_lottery:num_{$val}_294";
	$redis -> setx('incr',$imsi_num,100);
}

$imsi_key = array_rand($b);
$imsi = $b[$imsi_key];
$imsi_num = "schoolseason_lottery:num_{$imsi}_{$aid}";
$imsi_info = "schoolseason_lottery:info_{$imsi_{$aid}}";
if($imsi){
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	if($now_num >= 0){
		//抽奖
		load_helper('task');
		$task_client = get_task_client();
		$the_award = $task_client->do('schoolseason_lottery',$imsi);
		$the_award = json_decode($the_award,true);
		$num_where = array('imsi' => $imsi);
		$num_data = array(
			'lottery_num' => $now_num,
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
		echo 400;
		return 400;
	}
}
