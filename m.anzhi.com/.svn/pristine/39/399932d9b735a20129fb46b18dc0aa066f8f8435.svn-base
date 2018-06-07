<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
$sid = $_GET['sid'];
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$model = new GoModel();
$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 3600,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);
$page_option = array(
	'where' => array(
		'ap_id' => $activity_result['activity_end_id']
	),
	'cache_time' => 3600,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);

if($page_result['show_award'] == 1){
	$award_option = array(
		'where' => array(
			'aid' => $aid
		),
		'cache_time' => 3600,
		'table' => 'gm_lottery_award'
	);
	$award_result = $model -> findAll($award_option,'lottery/lottery');
	$this -> assign('award_result',$award_result);
}
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['page_result'] = $page_result;
$tplObj -> display('coactivity_award_show.html');
