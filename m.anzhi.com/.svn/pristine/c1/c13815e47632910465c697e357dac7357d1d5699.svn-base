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
$tab = rand(1,5);
$aid = $_GET['aid'];
$imsi = $_SESSION['USER_IMSI'];
$imsi_visit = "schoolseason_lottery:visit_{$imsi}_{$aid}";
$imsi_num = "schoolseason_lottery:num_{$imsi}_{$aid}";
$imsi_info = "schoolseason_lottery:info_{$imsi}_{$aid}";
$version_code = $_SESSION['VERSION_CODE'];
if($imsi && strlen($imsi) == 15 && $imsi != '000000000000000'){
	$alone_update = $_SESSION['alone_update'];
	if($alone_update == 1 && $version_code < 5500){  //独立更新
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp','>=5500'),
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
	}elseif($alone_update == 0 && $version_code < 5500){  //非独立更新
		$channel_status = 200;
		$resultanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array(
			'PACKAGE_NAME' => 'cn.goapk.market',
			'VR' => 3,
		));
		$tplObj -> out['channel_status'] = $channel_status;
		$tplObj -> out['resultanzhi'] = $resultanzhi;
		$log_data = array(
			'activity_id' => $aid,
			'imsi' => $imsi,
			'imei' => $_SESSION['USER_IMEI'],
			'device_id' => $_SESSION['DEVICEID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'key' => 'update_page'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	}else{
		$log_data = array(
			'activity_id' => $aid,
			'imsi' => $imsi,
			'imei' => $_SESSION['USER_IMEI'],
			'device_id' => $_SESSION['DEVICEID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'key' => 'show_homepage'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));
	}
}else{
	$tplObj -> out['status'] = 200;
}
$time = date('Ymd');
$my_visit = $redis -> gethash($imsi_visit,$time);
if(!$my_visit){
	$redis -> sethash($imsi_visit,array($time => 1));
	$all_visit = $redis -> gethash($imsi_visit);
	$now_num = $redis -> setx('incr',$imsi_num,0);
	$where = array(
		'imsi' => $imsi
	);
	if(count($all_visit) == 5){
		$now_num = $redis -> setx('incr',$imsi_num,2);
	}else if(count($all_visit) == 10){
		$now_num = $redis -> setx('incr',$imsi_num,3);
	}else if(count($all_visit) == 15){
		$now_num = $redis -> setx('incr',$imsi_num,4);
	}
	$data = array(
		'num' => $now_num,
		'update_tm' => time(),
		'__user_table' => 'schoolseason_lottery_num'
	);
	$model -> update($where,$data,'lottery/lottery');
}

$my_info = $redis -> gethash($imsi_info);

if($my_info){
	header("Location:http://promotion.anzhi.com/lottery/schoolseason_lottery.php?sid=".$_GET['sid']."&aid=".$aid."&tab=".$tab);
}
//$redis -> setx('incr',$imsi_num,100);
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $aid;
$tplObj -> out['tab'] = $tab;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('schoolseason_index.html');










