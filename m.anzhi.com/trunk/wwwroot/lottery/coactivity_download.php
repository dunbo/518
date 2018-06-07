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
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$old_package =$_GET['package'];
$package = $redis->get("real_package:post_old_package_{$old_package}:");
if(!$package)
{
	$package = get_real_package($_GET['package']);
}
$imsi = $_SESSION['USER_IMSI'];

brush_second_do($aid,1);

$imsi_package = "general_lottery:{$imsi}_package_{$aid}";
$imsi_num = "general_lottery:{$imsi}_num_{$aid}";
$share_must = "general_lottery:share_must_{$aid}";
$the_today = "general_lottery:day_{$imsi}_{$aid}";
$all_share = $redis -> gethash($share_must);
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
		'ap_id' => $activity_result['activity_page_id'],
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);

$time=time();

$down_key_today = 'gen_brush:down:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
$down_key_today2 = 'gen_brush:down2:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
$res=$redis->setnx($down_key_today,$time);
$redis->expire($down_key_today,86400*30);
if($res===false){
    $ret=$redis->setnx($down_key_today2,$time);
    $redis->expire($down_key_today2,86400*30);
    if($ret===false){
        $down_time = $redis->get($down_key_today);
        $down_time2 = $redis->get($down_key_today2);
        if($down_time==$down_time2&&$down_time2==$time){
            permanentlog('activity_'.$aid.'.log', 1);
            //黑名单
            $black_imsi_lottery = 'gen_brush:black:aid:'.$aid.'imsi:'.$imsi;
            $res=$redis->setnx($black_imsi_lottery,1);
            $redis->expire($black_imsi_lottery,86400*60);
        }
    }
}

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => $time,
	'package' => $old_package,
	'real_package' =>$package,
    'users'=> '',
    'uid'=> '',
	'key' => 'download_soft'
);

permanentlog('activity_'.$aid.'.log', json_encode($log_data));


if($page_result['lottery_style'] !=4&&$page_result['must_share'] == 1 && !$all_share[$imsi]){
	echo 200;
	return 200;
	exit;
}

$today = date("Ymd");
$today_num = $redis -> gethash($the_today);
if(!$today_num){
	$today_num[$today] = 0;
}
$lottery_num_limit = $page_result['lottery_num_limit'];
$old_package = $redis -> gethash($imsi_package);
if(!$old_package[$package]){
	$redis -> sethash($imsi_package,array($package => 1));
	if($page_result['get_lottery_type'] == 1 && ($today_num[$today] < $lottery_num_limit)){
		$now_num = $redis -> setx('incr',$imsi_num,1);
		$today_num_add = $today_num[$today] + 1;
		$today_num[$today] = $today_num_add;
		$redis -> sethash($the_today,$today_num);
		$where = array(
			'imsi' => $imsi,
			'aid' => $aid
		);
		$data = array(
			'lottery_num' => $now_num,
			'update_tm' => time(),
			'__user_table' => 'gm_lottery_num'
		);
		$num_result = $model -> update($where,$data,'lottery/lottery');
		if(!$num_result){
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'imsi' => $imsi,
				'aid' => $aid,
				'__user_table' => 'gm_lottery_num'
			);
			$num_results = $model -> insert($data,'lottery/lottery');
		}
	
		echo $now_num;
		return $now_num;
	}else{
		echo 200;
		return 200;
	}
}else{
	echo 200;
	return 200;
}




