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

$alone_update = $_SESSION['alone_update'];
$resultanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array(
	'PACKAGE_NAME' => 'cn.goapk.market',
	'VR' => 3,
));
$resultanzhi['SOFT_SIZES'] = formatFileSize('',$resultanzhi['SOFT_SIZE']);
$imei = $_SESSION['USER_IMEI'];
if($imei){
	if($alone_update == 1 && $_SESSION['VERSION_CODE'] < 5300){
		$channel_option = array(
			'where' => array(
				'cid' => $cid,
				'status' => 1,
				'version_code' => array('exp','>=5300'),
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
	}elseif($alone_update == 0 && $_SESSION['VERSION_CODE'] < 5300){
		$channel_status = 200;
		$tplObj -> out['channel_status'] = $channel_status;
		$tplObj -> out['resultanzhi'] = $resultanzhi;
	}
}
if($_GET['pid']){
	$pid = $_GET['pid'];
}elseif($_GET['aid']){
	$aid = $_GET['aid'];
	$activity_option = array(
		'where' => array(
			'id' => $aid
		),
		'cache_time' => 1200,
		'table' => 'sj_activity'
	);
	$activity_result = $model -> findOne($activity_option);
	$tplObj -> out['activity_result'] = $activity_result;
	$pid =  $activity_result['activity_page_id'];
}

$page_option = array(
	'where' => array(
		'ap_id' => $pid,
	),
	'cache_time' => 1200,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);

$page_result['share_text'] = str_replace("\r", "", $page_result['share_text']);
$page_result['share_text'] = str_replace("\n", "", $page_result['share_text']);
$page_result['share_text'] = str_replace("\r\n", "", $page_result['share_text']);

$grade_option = array(
	'where' => array(
		'ap_id' => $pid,
		'status' => 1
	),
	'cache_time' => 1200,
	'order' => 'like_grade',
	'table' => 'sj_setlike_grade'
);
$grade_result = $model -> findAll($grade_option);
$grade_count = count($grade_result);

$imsi = $_SESSION['USER_IMSI'];

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'show_homepage'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

$imei = $_SESSION['USER_IMEI'];

$today = date('Ymd');
$imei_num = "set_like:{$today}_{$imei}_{$aid}";
$all_imei = "set_like:imei_{$today}_{$aid}";
$all_imeis = $redis -> gethash($all_imei);
if(!$all_imeis[$imei]){
	$redis -> setx('incr',$imei_num,$page_result['like_limit']);
	$all_imeis[$imei] = 1;
	$redis -> sethash($all_imei,$all_imeis);
}
$my_num = $redis -> setx('incr',$imei_num,0);
$all_num = "set_like:all_num_{$aid}";
$all = $redis -> setx('incr',$all_num,0);
$the_telephone = "set_like:telephone_{$aid}";
$all_telephone = $redis -> gethash($the_telephone);
if($all_telephone[$imei]){
	$is_telephone = 1;
}else{
	$is_telephone = 2;
}

if(!$_GET['sid']){
	$tplObj -> out['resultanzhi'] = $resultanzhi;
}
if(!$all){
	$all_option = array(
		'where' => array(
			'aid' => $aid
		),
		'table' => 'setlike_num'
	);
	$all_result = $model -> findOne($all_option);
	$redis -> setx('incr',$all_num,$all_result['num']);
}

//$redis -> setx('incr',$all_num,200000);
//$redis -> setx('incr',$imei_num,10);

$all = $redis -> setx('incr',$all_num,0);
$is_max = $page_result['is_max'];
foreach($grade_result as $key => $val){
	$val['grade_values'] = $val['grade_value']*10000;
	if($is_max && $val['like_grade'] == $is_max){
		if($page_result['end_tm'] < time()){
			$my_max = $val['grade_values'];
			$my_prize_end = $val['grade_prize'];
		}
	}
	$grade_result[$key] = $val;
}

if($my_max){
	$all = $my_max;
}else{
	$the_max = $grade_result[$grade_count - 1]['grade_value'] * 10000;
	if($all > $the_max){
		$all = $the_max;
	}else{
		$all = $all;
	}
}

if($all < $grade_result[0]['grade_values'] && $page_result['start_tm'] <= time()){
	$all = $grade_result[0]['grade_values'] - 2000;
}

if(!$is_max){
	//未设集赞结果：每个阶段平均为5份值，计算每份具体值，根据实际集赞值计算出高度；
	for($i=0;$i<$grade_count;$i++){
		$mean = floor(($grade_result[$i]['grade_values'] - $grade_result[$i-1]['grade_values'])/5);
		$grade_result[$i]['mean'] = $mean;
	}

	foreach($grade_result as $key => $val){
		if($key == 0){
			for($i=0;$i<4;$i++){
				if($i == 0){
					$val['mean_arr'][$i] = $val['mean'];
				}else{
					$val['mean_arr'][$i] = $val['mean'] + $val['mean_arr'][$i - 1];
				}
			}
		}else{
			for($i=0;$i<4;$i++){
				if($i == 0){
					$val['mean_arr'][$i] = $val['mean'] + $grade_result[$key - 1]['grade_values'];
				}else{
					$val['mean_arr'][$i] = $val['mean'] + $val['mean_arr'][$i - 1];
				}
			}
		}
		$grade_result[$key] = $val;
	}

	if($grade_count == 3){
		$height = 36;
		$mean_px = 7;
	}elseif($grade_count == 4){
		$height = 27;
		$mean_px = 5;
	}elseif($grade_count == 5){
		$height = 22;
		$mean_px = 5;
	}

	foreach($grade_result as $key => $val){
		if($all < $grade_result[0]['grade_values']){
			foreach($grade_result[0]['mean_arr'] as $k => $v){
				if($all < $grade_result[0]['mean_arr'][0]){
					$my_height = 0;
				}elseif($all >= $v && $all < $grade_result[0]['mean_arr'][$k+1]){
					$my_height = $mean_px*($k + 1);
				}elseif($all >= $v && !$grade_result[0]['mean_arr'][$k+1]){
					$my_height = $mean_px*($k + 1);
				}
			}
			//$my_height = 0;
			$my_prize = $grade_result[0]['grade_prize'];
		}elseif($all >= $grade_result[$key-1]['grade_values'] && $all < $val['grade_values']){
			foreach($val['mean_arr'] as $k => $v){
				if($all < $v && $k == 0){
					$my_height = $height*($key);
				}elseif($all >= $v && $all < $val['mean_arr'][$k+1]){
					$my_height = $height*($key) + $mean_px*($k + 1);
				}elseif($all > $v && !$val['mean_arr'][$k+1]){
					$my_height = $height*($key) + $mean_px*($k + 1);
				}
			}
			$my_prize = $grade_result[$key-1]['grade_prize'];
		}elseif($all >= $grade_result[$grade_count - 1]['grade_values']){
			$my_height = 110;
			$my_prize = $grade_result[$grade_count - 1]['grade_prize'];
		}
	}
	$my_heights = 110 - $my_height;
}else{
	//设集赞结果：根据所有集赞阶段和设置的集赞结果计算出集赞结果的具体高度值并等分10份；
	//整体集赞时间平均为10等份，每过一个等级时间，高度自动增长到对应高度；
	$start_tm = $page_result['start_tm'];
	$end_tm = $page_result['end_tm'];
	$dif_tm = $end_tm - $start_tm;
	$raido_tm = floor($dif_tm/10);
	for($i=0; $i<10; $i++){
		$time_arr[$i] = $start_tm + $raido_tm*$i;
	}
	if(($is_max == 3 && $grade_count == 3) || ($is_max == 4 && $grade_count == 4) || ($is_max == 5 && $grade_count == 5)){
		$top = 110;
	}elseif($is_max == 1 && $grade_count == 3){
		$top = 37;
		$top_go = 56;
	}elseif($is_max == 2 && $grade_count == 3){
		$top = 74;
		$top_go = 93;
	}elseif($is_max == 1 && $grade_count == 4){
		$top = 28;
		$top_go = 42;
	}elseif($is_max == 2 && $grade_count == 4){
		$top = 55;
		$top_go = 69;
	}elseif($is_max == 1 && $grade_count == 5){
		$top = 22;
		$top_go = 33;
	}elseif($is_max == 2 && $grade_count == 5){
		$top = 44;
		$top_go = 55;
	}elseif($is_max == 3 && $grade_count == 4){
		$top = 82;
		$top_go = 96;
	}elseif($is_max == 3 && $grade_count == 5){
		$top = 66;
		$top_go = 77;
	}elseif($is_max == 4 && $grade_count == 5){
		$top = 88;
		$top_go = 99;
	}
	$radio_height = floor($top/10);
	$begin_height = 0;

	for($j=0; $j<10; $j++){
		$height_arr[$j] = $begin_height + $radio_height * ($j+1);
	}

	for($n=0; $n<count($time_arr); $n++){
		if((time() >= $time_arr[$n] && time() < $time_arr[$n + 1] && $time_arr[$n + 1]) || (time() >= $time_arr[$n] && !$time_arr[$n + 1])){
			$my_height = $height_arr[$n];
		}
	}
	if(time() > $end_tm){
		$my_height = $top_go;
	}

	$my_heights = 110 - $my_height;
	if($my_height == 0){
		$my_heights = 103;
	}
	
	//当高度大于某一个阶段值则改变对应阶段颜色
	if($grade_count == 3){
		$thr_top = floor(110/3);
	}elseif($grade_count == 4){
		$thr_top = floor(110/4);
	}elseif($grade_count == 5){
		$thr_top = floor(110/5);
	}
	for($i = 0; $i < $grade_count; $i++){
		$thr_height[$i] = $thr_top*($i+1);
	}
	for($i = 0; $i < $grade_count; $i++){
		if(($my_height >= $thr_height[$i] && $my_height < $thr_height[$i + 1] && $thr_height[$i + 1]) || ($my_height >= $thr_height[$i] && !$thr_height[$i + 1])){
			$tops = $i;
		}elseif($my_height < $thr_height[0]){
			$tops = 0;
		}
	}
	
	$tplObj -> out['tops'] = $tops;
}

if($my_height < 30) {
	$my_top = 50;
}else{
	$my_top = $my_heights + 10;
}
if($is_max){
	$my_prize = $my_prize_end;
}else{
	$my_prize = $my_prize;
}
$the_package = "set_like:package_{$aid}";
$all_package = $redis -> gethash($the_package);
if($all_package[$imei]){
	$is_download = 1;
}else{
	$is_download = 2;
}

$tplObj -> out['is_download'] = $is_download;
$tplObj -> out['is_telephone'] = $is_telephone;
$tplObj -> out['my_prize'] = $my_prize;
$tplObj -> out['my_num'] = $my_num;
$tplObj -> out['my_heights'] = $my_heights;
$tplObj -> out['my_top'] = $my_top;
$tplObj -> out['is_max'] = $is_max;
$tplObj -> out['all_w'] = $all_w;
$tplObj -> out['all_num'] = $all;
$tplObj -> out['grade_result'] = $grade_result;
$tplObj -> out['grade_count'] = $grade_count;
$tplObj -> out['now'] = time();
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $_GET['aid'];
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['activity_url'] = $configs['activity_url'];
$tplObj -> out['page_result'] = $page_result;
$tplObj -> display("setlike_lottery.html");

