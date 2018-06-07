<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');

$model = new GoModel();
session_begin();
$imsi = $_SESSION['USER_IMSI'];
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$model = new GoModel();

$prize_option = array(
	'where' => array(
		'A.imsi' => $imsi,
		'A.aid' => $aid,
		'A.status' => 1
	),
	'table' => 'gm_lottery_award AS A',
	'field' => 'A.id,A.level,A.telphone,A.name as my_name,A.address,A.time as my_time,A.pid,B.name,B.type,B.desc',
	'join' => array(
		'gm_lottery_prize AS B' => array(
			'on' => array('A.pid', 'B.pid'),
		),
	)
);
$prize_result = $model -> findAll($prize_option,'lottery/lottery');

$probability_option = array(
	'where' => array(
		'A.imsi' => $imsi,
		'A.aid' => $aid,
		'A.status' => 1
	),
	'table' => 'gm_virtual_prize AS A',
	'field' => 'A.pid,A.first_text,A.second_text,A.third_text,A.pid,A.status,A.aid,A.imsi,A.update_tm as my_time,B.pid,B.name,B.type,B.level,B.aid,B.status,B.desc',
	'join' => array(
		'gm_lottery_prize AS B' => array(
			'on' => array('A.pid', 'B.pid')
		)
	)
);
$probability_result = $model -> findAll($probability_option,'lottery/lottery');

foreach($probability_result as $key => $val){
	$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $val['third_text']));
	$val['softid'] = $softinfo['ID'];
	$val['soft_name'] = $softinfo['SOFT_NAME'];
	$val['versioncode'] = $softinfo['SOFT_VERSION_CODE'];
	$val['size'] = $softinfo['SOFT_SIZE'];
	$probability_result[$key] = $val;
}

if($prize_result && $probability_result){
	$all_result = array_merge($prize_result,$probability_result);
}elseif(!$prize_result && $probability_result){
	$all_result = $probability_result;
}elseif($prize_result && !$probability_result){
	$all_result = $prize_result;
}

foreach($all_result as $key => $val){
	$result_time[] = $val['level'];
}

array_multisort($result_time,SORT_NUMERIC,SORT_ASC,$all_result);

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
if($page_result['dep_type']==1){
    $tplObj -> out['gift_name'] = '卡号';
}else{
    $tplObj -> out['gift_name'] = '礼包';
}

$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['page_result'] = $page_result;
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['all_result'] = $all_result;
$tplObj -> display('coactivity_prize.html');






