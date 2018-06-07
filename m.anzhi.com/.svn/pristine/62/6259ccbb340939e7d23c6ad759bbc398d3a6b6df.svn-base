<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = 221;

$b = array("460042359383380","460086504303738","460011705796755","460066600163811","460073760208920","460092178990235","460034351743375","460086530305114","460024872225457","460071144031826","460030233084908","460088584916656","460013804672458","460013855218561","460039294304493","460073258131998","460070875446228","460059990915940","460015370965804","460094962560934","460092343491874","460090393188926","460086690816967","460053412941116","460039791800077","460059800300104","460086622319091","460087246883404","460078192938724","460078140494581","460019626873075","460058071021740","460034240615977","460025858003948","460099604764641","460043462001704","460073659513597","460043237969651","460046116390344","460070159321734","460055352379735","460097129613822","460016603109971","460013098810431","460040402766596","460094160059713","460082125214678","460066191885583","460065071217878","460059791540009");
//写次数
/*
foreach($b as $key => $val){
	$imsi_num = "general_lottery:{$val}_num_{$aid}";
	$redis -> delete($imsi_num);
	$redis -> setx('incr',$imsi_num,100);
}
*/
$imsi_key = array_rand($b);
$imsi = $b[$imsi_key];
$imsi_num = "general_lottery:{$imsi}_num_{$aid}";
if($imsi){
	$now_num = $redis -> setx('incr',$imsi_num,-1);
	if($now_num >= 0){
		//抽奖
		load_helper('task');
		$task_client = get_task_client();
		$need = array('imsi' => $imsi,'aid' => $aid);
		$the_award = $task_client->do('general_lottery',json_encode($need));
		$num_where = array('imsi' => $imsi,'aid' => $aid);
		$num_data = array(
			'lottery_num' => array('exp','lottery_num-1'),
			'update_tm' => time(),
			'__user_table' => 'gm_lottery_num'
		);
		$num_result = $model -> update($num_where,$num_data,'lottery/lottery');
					
		$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'users' => '',
			'uid' => '',
			'key' => 'lottery'
		);

		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		if($the_award == -1){
			$prize_option = array(
				'where' => array(
					'aid' => $aid,
					'type' => 3,
					'status' => 1
				),
				'cache_time' => 86400,
				'table' => 'gm_lottery_prize'
			);
			$prize_result = $model -> findAll($prize_option,'lottery/lottery');
			$my_prize = array_rand($prize_result);
			$my_award = $prize_result[$my_prize]['level'];
			$data = array($the_award,$now_num,$my_award);
			echo json_encode($data);
			return json_encode($data);
		}else{
			$the_award = json_decode($the_award,true);
			$pid = $the_award['pid'];
				if($the_award['type'] == 1){
					$log_data = array(
					'imsi' => $imsi,
					'device_id' => $_SESSION['DEVICEID'],
					'activity_id' => $aid,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'sid' => $_GET['sid'],
					'time' => time(),
					'award_level' => $the_award['level'],
					'pid' => $pid,
					'user' => '',
					'uid' => '',
					'name' => '',
					'telephone' => '',
					'address' => '',
					'package' => '',
					'gift' => '',
					'users' => '',
					'uid' => '',
					'lottery_type' => '',
					'award_name' => $the_award['name'],
					'key' => 'award'
				);
				permanentlog('activity_'.$aid.'.log', json_encode($log_data));
				$data = array($the_award['level'],$now_num,$the_award['name'],$the_award['type']);
			}elseif($the_award['type'] == 2){
				$data = array($the_award['level'],$now_num,$the_award['name'],$the_award['type'],$the_award['gift_number']);
				if($the_award['gift_number']['third_text']){
					$the_package = $the_award['gift_number']['third_text'];
				}else{
					$the_package = '';
				}
				$pid = $the_award['pid'];
				$activity_option = array(
					'where' => array(
						'id' => $aid
					),
					'cache_time' => 86400,
					'table' => 'sj_activity'
				);
				$activity_result = $model -> findOne($activity_option);
				$page_option = array(
					'where' => array(
						'ap_id' => $activity_result['activity_page_id']
					),
					'cache_time' => 86400,
					'table' => 'sj_activity_page'
				);
				$page_result = $model -> findOne($page_option);
				$log_data = array(
					'imsi' => $imsi,
					'device_id' => $_SESSION['DEVICEID'],
					'activity_id' => $aid,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'sid' => $_GET['sid'],
					'time' => time(),
					'award_level' => $the_award['level'],
					'pid' => $pid,
					'name' => '',
					'telphone' => '',
					'address' => '',
					'package' => $the_package,
					'gift' => $the_award['gift_number']['first_text'],
					'users' => '',
					'uid' => '',
					'lottery_type' => $page_result['lottery_style'],
					'award_name' => $the_award['name'],
					'key' => 'award'
				);
				permanentlog('activity_'.$aid.'.log', json_encode($log_data));
			}

			echo json_encode($data);
			return json_encode($data);
		}
	}else{
		$now_num = $redis -> setx('incr',$imsi_num,1);
		echo $now_num;
		return $now_num;
	
	}
		
}