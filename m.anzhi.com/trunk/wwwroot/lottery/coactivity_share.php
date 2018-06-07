<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
session_begin();
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$time=time();

/*
$share_key_today = 'gen_brush:share:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
$redis->setnx($share_key_today,$time);
$redis->expire($share_key_today,86400*30);
*/

brush_second_do($aid,1);
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => $time,
	'users' => '',
	'uid' => '',
	'key' => 'share_soft'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));
$imsi_num = "general_lottery:{$imsi}_num_{$aid}";
$share_imsi = "general_lottery:share_{$imsi}_{$aid}";
$share_must = "general_lottery:share_must_{$aid}";
$model = new GoModel();
$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);
$page_option = array(
	'where' => array(
		'ap_id' => $activity_result['activity_page_id']
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);
if($page_result['must_share'] == 1){
	$my_share = $redis -> gethash($share_must);
	if(!$my_share[$imsi]){
		$redis -> sethash($share_must,array($imsi => 1));
	}
}

if($page_result['dep_type']==2)//游戏 supwater
{
    if($page_result['share_add_all']==1){
        $today = 'guding';
        $page_result['share_add']=1;
    }
}else{
    $today = date('Ymd');
}

if($page_result['share_add'] == 1 && $imsi){
	$my_share = $redis -> gethash($share_imsi,$today);
	if($my_share){
		echo 200;
		return 200;
	}else{
		$now_num = $redis -> setx('incr',$imsi_num,1);
		$where = array(
			'aid' => $aid,
			'imsi' => $imsi
		);
		$data = array(
			'lottery_num' => $now_num,
			'update_tm' => time(),
			'__user_table' => 'gm_lottery_num'
		);
		$result = $model -> update($where,$data,'lottery/lottery');
		if(!$result){
			$data = array(
				'aid' => $aid,
				'imsi' => $imsi,
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'__user_table' => 'gm_lottery_num'
			);
			$result = $model -> insert($data,'lottery/lottery');
		}
		$redis -> sethash($share_imsi,array($today => 1));
		echo $now_num;
		return $now_num;
	}
}else{
	echo 200;
	return 200;
}
