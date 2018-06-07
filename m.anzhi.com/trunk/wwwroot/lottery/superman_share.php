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

$key=get_key();
if($key)
{
	$info=$redis -> get($key);
	$info_arr=json_decode($info, true);
	$info_arr['get_share']=1;
	$save_info=$redis -> set($key,json_encode($info_arr));
}

$aid = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];
$cid = $_SESSION['MODEL_CID'];
if($imsi && $imsi != '000000000000000' && strlen($imsi) == 15){
	$alone_update = $_SESSION['alone_update'];
	if($alone_update == 1 && $_SESSION['VERSION_CODE'] < 5410){
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp','>=5410'),
				'limit_rules' => array('exp'," ='' or limit_rules is null "),
			),
			'cache_time' => 3601,
			'table' => 'sj_market',
		);
		$channel_result = $model -> findAll($channel_option);
		if($channel_result){
			$channel_status = 100;
		}else{
			$channel_status = 300;	
		}
		$tplObj -> out['channel_status'] = $channel_status;
	}elseif($alone_update == 0 && $_SESSION['VERSION_CODE'] < 5410){
		$log_data = array(
			'imsi' => $imsi,
			'imei' => $_SESSION['USER_IMEI'],
			'device_id' => $_SESSION['DEVICEID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'key' => 'update_show'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
		$channel_status = 200;
		$resultanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array(
			'PACKAGE_NAME' => 'cn.goapk.market',
			'VR' => 3,
		));
		$tplObj -> out['channel_status'] = $channel_status;
		$tplObj -> out['resultanzhi'] = $resultanzhi;

	}elseif($_SESSION['VERSION_CODE'] >= 5410){
		$log_data = array(
			'imsi' => $imsi,
			'imei' => $_SESSION['USER_IMEI'],
			'device_id' => $_SESSION['DEVICEID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'key' => 'index'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	}
	
	$imsi_num = "superman_lottery:num_{$imsi}_{$aid}";
	$imsi_info = "superman_lottery:info_{$imsi}_{$aid}";
	$imsi_share = "superman_lottery:share_{$imsi}_{$aid}";
	$imsi_msg = $redis -> gethash($imsi_share);
	$today = date('Ymd',time());

	if($imsi_msg[$today]){
		$tplObj -> out['share_status'] = 1;
	}else{
		$tplObj -> out['share_status'] = 2;
	}
	//最近中奖信息
	$all_award_option = array(
		'where' => array(
			'status' => 1,
			'award_level' => array('exp',' <= 3'),
		),
		'order' => 'time desc',
		'limit' => 10,
		'table' => 'superman_lottery_award',
	);
	$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');
	$award_info_option = array(
		'where' => array(
			'config_type' => 'SUPERMAN_AWARD',
			'status' => 1
		),
		'cache_time' => 86401,
		'table' => 'pu_config'
	);
	$award_info_result = $model -> findOne($award_info_option);
	$award_content = json_decode($award_info_result['configcontent'],true);
	if($all_award_result){
		foreach($all_award_result as $key => $val){
			$val['award'] = $award_content[$val['award_level']][1];
			$val['the_time'] = date('Y-m-d',$val['time']);
			$val['telephone'] = substr_replace($val['telephone'],'****',3,4);
			$all_award_result[$key] = $val;
		}
		$tplObj -> out['all_award_result'] = $all_award_result;
		$tplObj -> out['all_award_count'] = count($all_award_result);
	}
	$my_info = $redis -> gethash($imsi_info);
	//是否有中奖未填写信息
	if($my_info){
		$award_result = $my_info[1];
		header("location:/lottery/superman_lottery.php?sid={$sid}&aid={$aid}");
	}else{
		$award_option = array(
			'where' => array(
				'imsi' => $imsi,
				'status' => 0,
				'award_level' => array('exp','<=2')
			),
			'table' => 'superman_lottery_award'
		);
		$award_result = $model -> findOne($award_option,'lottery/lottery');
		if($award_result){
			$my_award = array($award_result['imsi'],$award_result['award_level'],$award_result['time'],$award_status['status']);
			$redis -> sethash($imsi_info,$my_award);
			header("location:/lottery/superman_lottery.php?sid={$sid}&aid={$aid}");
		}
	}
	
	//判断第几天访问活动
	$imsi_visit = "superman_lottery:visit_{$imsi}_{$aid}";
	
	$my_visit = $redis -> gethash($imsi_visit);
	$the_time = time();
	if(!$my_visit){
		$redis -> sethash($imsi_visit,array($the_time => $the_time));
	}else{
		$my_visit = array_reverse($my_visit);
		$before_time = $my_visit[0];
		$now = date('Ymd',$the_time);
		$before_times = date('Ymd',$before_time + 86400);
		$now_times = date('Ymd',$before_time);
		if($before_times == $now){
			$redis -> sethash($imsi_visit,array($the_time => $the_time));
			$now_visit = $redis -> gethash($imsi_visit);
			if(count($now_visit) == 3){
				$my_num = $redis -> setx('incr',$imsi_num,1);
			}elseif(count($now_visit) == 6){
				$my_num = $redis -> setx('incr',$imsi_num,2);
			}elseif(count($now_visit) == 9){
				$my_num = $redis -> setx('incr',$imsi_num,3);
			}elseif(count($now_visit) == 12){
				$my_num = $redis -> setx('incr',$imsi_num,4);
			}elseif(count($now_visit) == 15){
				$my_num = $redis -> setx('incr',$imsi_num,5);
			}else{
				$my_num = $redis -> setx('incr',$imsi_num,0);
			}
		}elseif($before_times != $now && $now_times != $now){
			$redis -> delete($imsi_visit);
			$redis -> sethash($imsi_visit,array($the_time => $the_time));
		}
	}
	
	$my_num_option = array(
		'where' => array(
			'imsi' => $imsi
		),
		'table' => 'superman_lottery_num'
	);
	$my_num_result = $model -> findOne($my_num_option,'lottery/lottery');
	$my_num = $redis -> setx('incr',$imsi_num,0);
	if(isset($my_num_result)){
		$where = array(
			'imsi' => $imsi
		);
		$data = array(
			'lottery_num' => $my_num,
			'update_tm' => time(),
			'__user_table' => 'superman_lottery_num'
		);
		$result = $model -> update($where,$data,'lottery/lottery');
	}else{
		$data = array(
			'imsi' => $imsi,
			'lottery_num' => $my_num,
			'update_tm' => time(),
			'__user_table' => 'superman_lottery_num'
		);
		$result = $model -> insert($data,'lottery/lottery');
	}
	$tplObj -> out['my_num'] = $my_num;
	$tplObj -> out['imsi_status'] = 200;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['aid'] = $aid;
	$tplObj -> out['static_url'] = $configs['static_url'];
}else{
	$tplObj -> out['imsi_status'] = 100;
}

$tplObj -> out['key'] = $key;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('superman_share.html');