<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
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
$page_result['ap_rule'] = htmlspecialchars_decode($page_result['ap_rule']);
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['page_result'] = $page_result;
$tplObj -> display("coactivity_rule.html");
