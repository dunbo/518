<?php

/*
活动测试--压测
*/

//1000，抽奖次数为0；300，抽奖次数为1；400,抽奖次数大于1；800,中奖了；

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('cache/lottery_redis');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
/* if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}

session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
	$imsi_redis = $imsi.":lottery";
	$imsi_num = $imsi.":lottery_num";
}
 */
 $imsi = $_GET['imsi'];
 $imsi_num = $imsi.":lottery_num";

if($imsi){
	//$now_num = $redis -> setx('incr',$imsi_num,-1);
	
	//if($now_num >= 0){
	
		//抽奖

		load_helper('task');
		$task_client = get_task_client();
		$the_award = $task_client->do('school_lottery',$imsi);
		$num_where = array('imsi' => $imsi);
		$num_data = array(
			'lottery_num' => 1000,
			'time' => time(),
			'__user_table' => 'school_lottery'
		);
		$num_result = $model -> update($num_where,$num_data,'lottery/lottery');

		if($the_award < 6 && $the_award > 0){
			if($the_award == 1){
				$award = 1;
				$awards = 2;
			}elseif($the_award == 2){
				$award = 2;
				$awards = 8;
			}elseif($the_award == 3){
				$award = 3;
				$awards = 4;
			}elseif($the_award == 4){
				$award = 4;
				$awards = 6;
			}elseif($the_award == 5){
				$award = 5;
				$awards = 1;
			}elseif($the_award == 6){
				$award = 6;
				$awards = 5;
			}
		}else{
			$award = 0;
		}
			
		if($award != 'no_award' && $imsi && $imsi != '000000000000000'){
			$award_level_option = array(
				'where' => array(
					'config_type' => 'ND_AWARD',
					'status' => 1
				),
				'table' => 'pu_config'
			);
			$award_level_result = $model -> findOne($award_level_option);
			$award_level_arr = json_decode($award_level_result['configcontent'],true);
			$award_level = $award_level_arr[$award][0];
			$prize = $award_level_arr[$award][1];
		}
	//}else{
	//	$now_num = $redis -> setx('incr',$imsi_num,1);
	//}
	
	echo $award;
file_put_contents('data_school.txt',$award . "\r\n",FILE_APPEND);
}
	/* $no_award_arr = array(3,7);
	if($now_num < 0){
		$now_num = $redis -> setx('incr',$imsi_num,1);
		$awards = 0;
		$notice = 1000;
	}elseif($now_num == 0){
		if($award == 'no_award'){
			$awards = $no_award_arr[array_rand($no_award_arr)];
			$notice = 300;
		}else{
			$notice = 900;
			$data = array($notice,$award_level,$prize,$awards,$now_num);
			echo json_encode($data);
			return json_encode($data);
		}
	}elseif($now_num >= 0){
		if($award == 'no_award'){
			$awards = $no_award_arr[array_rand($no_award_arr)];
			$notice = 400;
			$data = array($notice,$awards,$now_num);
			echo json_encode($data);
			return json_encode($data);
		}else{
			$notice = 800;
			$data = array($notice,$award_level,$prize,$awards,$now_num);
			echo json_encode($data);
			return json_encode($data);
		}
	} 
}*/