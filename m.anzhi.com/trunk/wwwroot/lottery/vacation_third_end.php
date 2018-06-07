<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 187;
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
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'table' => 'vacation_third_award'
);

$result = $model -> findAll($option,'lottery/lottery');

$content_option = array(
	'where' => array(
		'config_type' => 'VACATION_THIRD_AWARD',
		'status' => 1
	),
	'table' => 'pu_config'
);

$content_result = $model -> findOne($content_option);
$award_content = json_decode($content_result['configcontent'],true);
foreach($result as $key => $val){
	$val['the_telphone'] = substr_replace($val['telphone'],'****',3,4);
	$val['prize'] = $award_content[$val['award_level'] - 1][1];
	if($val['award_level'] == 1){
		$first_award[] = $val;
	}elseif($val['award_level'] == 2){
		$second_award[] = $val;
	}elseif($val['award_level'] == 3){
		$third_award[] = $val;
	}elseif($val['award_level'] == 4){
		$forth_award[] = $val;
	}elseif($val['award_level'] == 5){
		$fivth_award[] = $val;
	}
}

$tplObj -> out['first_award'] = $first_award;
$tplObj -> out['second_award'] = $second_award;
$tplObj -> out['third_award'] = $third_award;
$tplObj -> out['forth_award'] = $forth_award;
$tplObj -> out['fivth_award'] = $fivth_award;
$tplObj -> display("vacation_third_end.html");
