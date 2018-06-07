<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 186;
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

//查询该用户是否中过将未填写信息
$have_option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 0
	),
	'table' => 'vacation_lottery_award'
);
$have_result = $model -> findOne($have_option,'lottery/lottery');
if($have_result){
	header("location:http://promotion.anzhi.com/lottery/vacation_lottery_info.php?sid={$_GET['sid']}");
}

$option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'table' => 'vacation_lottery_award'
);

$result = $model -> findAll($option,'lottery/lottery');

$content_option = array(
	'where' => array(
		'config_type' => 'VACATION_LOTTERY_SECOND',
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
	}elseif($val['award_level'] == 6){
		$sixth_award[] = $val;
	}elseif($val['award_level'] == 7){
		$seventh_award[] = $val;
	}elseif($val['award_level'] == 8){
		$eighth_award[] = $val;
	}
}

$tplObj -> out['first_award'] = $first_award;
$tplObj -> out['first_award_count'] = count($first_award);
$tplObj -> out['second_award'] = $second_award;
$tplObj -> out['second_award_count'] = count($second_award);
$tplObj -> out['third_award'] = $third_award;
$tplObj -> out['third_award_count'] = count($third_award);
$tplObj -> out['forth_award'] = $forth_award;
$tplObj -> out['forth_award_count'] = count($forth_award);
$tplObj -> out['fivth_award'] = $fivth_award;
$tplObj -> out['fivth_award_count'] = count($fivth_award);
$tplObj -> out['sixth_award'] = $sixth_award;
$tplObj -> out['sixth_award_count'] = count($sixth_award);
$tplObj -> out['seventh_award'] = $seventh_award;
$tplObj -> out['seventh_award_count'] = count($seventh_award);
$tplObj -> out['eighth_award'] = $eighth_award;
$tplObj -> out['eighth_award_count'] = count($eighth_award);
$tplObj -> display("vacation_lottery_end.html");
