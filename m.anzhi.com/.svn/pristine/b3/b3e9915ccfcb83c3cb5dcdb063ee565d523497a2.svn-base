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
$imsi = $_SESSION['USER_IMSI'];
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];
if($alone_update == 1 && $_SESSION['VERSION_CODE'] < 5400){
	$channel_option = array(
		'where' => array(
			'cid' => $cid,
			'status' => 1,
			'version_code' => array('exp','>=5400'),
			'limit_rules' => array('exp'," ='' or limit_rules is null "),
		),
		'cache_time' => 3600,
		'table' => 'sj_market',
	);
	$channel_result = $model -> findAll($channel_option);
	if(!$channel_result){
		$channel_status = 100;
	}else{
		$channel_status = 200;	
	}
	$tplObj -> out['channel_status'] = $channel_status;
}

if($imsi){
	if($_SESSION['alone_update']==0 && $_SESSION['VERSION_CODE'] < 5400){
		$version_status = 200;
		$intro_option = array(
			'where' => array(
				'package' => 'cn.goapk.market'
			),
			'field' => 'softid,softname,version_code',
			'order' => 'softid DESC',
			'limit' => 1,
			'cache_time' => 86400,
			'table' => 'sj_soft'
		);
		$intro_result = $model -> findOne($intro_option);

		$intro_size_option = array(
			'where' => array(
				'softid' => $intro_result['softid']
			),
			'field' => 'filesize',
			'table' => 'sj_soft_file',
			'cache_time' => 86400
		);
		$intro_size_result = $model -> findOne($intro_size_option);
		$intro_result['soft_sizes'] = formatFileSize('',$intro_size_result['filesize']);
		$intro_result['soft_size'] = $intro_size_result['filesize'];
	}else{
		$version_status = 1000;
	}
	$tplObj -> out['version_status'] = $version_status;
	$tplObj -> out['intro_result'] = $intro_result;
}

$imsi_redis = $imsi.":lottery_{$active_id}";
$imsi_num = $imsi.":lottery_num_{$active_id}";
$imsi_info = $imsi.":info_{$active_id}";

//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'limit' => 10,
	'cache_time' => 600,
	'table' => 'school_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

if($all_award_result){
	$award_config_option = array(
		'where' => array(
			'config_type' => 'SCHOOL_AWARD',
			'status' => 1
		),
		'cache_time' => 86400,
		'table' => 'pu_config'
	);
	$award_config_result = $model -> findOne($award_config_option);
	$award_level = json_decode($award_config_result['configcontent'],true);
	foreach($all_award_result as $key => $val){
		$val['award'] = $award_level[$val['award']][1];
		$val['the_time'] = date('Y-m-d',$val['time']);
		$val['telphone'] = substr_replace($val['telphone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
}

$tplObj -> out['all_award_result'] = $all_award_result;
$tplObj -> out['all_award_count'] = count($all_award_result);

if(!$imsi || $imsi == '000000000000000'){
	$tplObj -> out['status'] = 100;
}else{
	//查询该imsi是否有中过奖
	$my_info = $redis -> gethash($imsi_info);

	if($my_info){
		$award_result = $my_info[1];
		$result = $my_info[0];
	}else{
		$award_option = array(
			'where' => array(
				'imsi' => $imsi,
				'status' => 0
			),
			'table' => 'school_award'
		);
		$award_results = $model -> findOne($award_option,'lottery/lottery');
		
		$option = array(
			'where' => array(
				'imsi' => $imsi,
			),
			'table' => 'school_lottery'
		);
		$results = $model -> findOne($option,'lottery/lottery');
		if(!$award_results){
			$award_result_go = 0;
		}
		$the_info = array($results['time'],$award_result_go);
		$redis -> sethash($imsi_info,$the_info);
		$award_result = $award_results['award'];
		$result = $results['time'];
	}
	if($award_result){
		$award_level_option = array(
			'where' => array(
				'config_type' => 'SCHOOL_AWARD',
				'status' => 1
			),
			'cache_time' => 86400,
			'table' => 'pu_config'
		);
		$award_level_result = $model -> findOne($award_level_option);
		$award_level_arr = json_decode($award_level_result['configcontent'],true);
		$award_level = $award_level_arr[$award_result][0];
		$prize = $award_level_arr[$award_result][1];
		$tplObj -> out['award_level'] = $award_level;
		$tplObj -> out['prize'] = $prize;
		if($award_result <= 3){
			$tplObj -> out['status'] = 2000;
		}elseif($award_result >= 4 && $award_result <= 6){
			$tplObj -> out['status'] = 3000;
		}
	}else{
		$now_time = strtotime(date('Y-m-d 00:00:00',time()));
		//初始化剩余抽奖次数
		if($result < $now_time){
			$num = 1;
			$my_num = $redis -> setx('incr',$imsi_num,$num);
			$update_data = array(
				'lottery_num' => $my_num,
				'time' => time(),
				'__user_table' => 'school_lottery'
			);
			$update_where = array(
				'imsi' => $imsi
			);
			$update_result = $model -> update($update_where,$update_data,'lottery/lottery');
			$the_old_info = $redis -> gethash($imsi_info);
			$the_info_arr = array(time(),$the_old_info[1]);
			$redis -> sethash($imsi_info,$the_info_arr,0,false,true);
			if(!$update_result){
				$insert_data = array(
					'lottery_num' => $my_num,
					'time' => time(),
					'imsi' => $imsi,
					'__user_table' => 'school_lottery'
				);
				$insert_data = $model -> insert($insert_data,'lottery/lottery');
			}
		}
		$tplObj -> out['status'] = 200;
	}
	$my_num = $redis -> setx('incr',$imsi_num,0);
	if(!$my_num){
		$num_option = array(
			'where' => array(
				'imsi' => $imsi,
			),
			'table' => 'school_lottery'
		);
		$num_result = $model -> findOne($num_option,'lottery/lottery');
		$my_num = $num_result['lottery_num'];
		$my_num = $redis -> setx('incr',$imsi_num,$my_num);
	}
	$tplObj -> out['my_num'] = $my_num;
}

$tplObj -> out['img_url'] = "http://apk.goapk.com/";
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display('school_lottery.html');