<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
session_begin();
$imsi = $_SESSION['USER_IMSI'];
$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 1
	),
	'order' => 'create_tm DESC',
	'table' => 'friends_lottery_award'
);

$result = $model -> findAll($option,'lottery/lottery');

$award_option = array(
	'where' => array(
		'config_type' => 'FRIENDS_AWARD',
		'status' => 1
	),
	'cache_time' => 86400,
	'table' => 'pu_config'
);

$award_result = $model -> findOne($award_option);
$award_content = json_decode($award_result['configcontent'],true);

foreach($result as $key => $val){
	$val['prize'] = $award_content[$val['award_level']][0];
	$result[$key] = $val;
}

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['result'] = $result;
$tplObj -> display("friends_prize.html");

