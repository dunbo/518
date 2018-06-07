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
$imsi = $_SESSION['USER_IMSI'];

$option = array(
	'where' => array(
		'imsi' => $imsi,
		'status' => 1
	),
	'order' => 'award_level,create_tm DESC',
	'table' => 'schoolseason_lottery_award'
);
$result = $model -> findAll($option,'lottery/lottery');

$content_option = array(
	'where' => array(
		'config_type' => 'SCHOOLSEASON_AWARD',
		'status' => 1
	),
	'table' => 'pu_config'
);

$content_result = $model -> findOne($content_option);
$award_content = json_decode($content_result['configcontent'],true);
foreach($result as $key => $val){
	$val['level'] = $award_content[$val['award_level']][0];
	$val['prize'] = $award_content[$val['award_level']][1];
	$result[$key] = $val;
}

$result_soft = gomarket_action('soft.GoGetSoftDetailPackage', array(
	'PACKAGE_NAME' => 'com.gumichina.wcat.anzhi',
	'VR' => 3,
));

$tplObj -> out['result_soft'] = $result_soft;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['result'] = $result;
$tplObj -> display("schoolseason_prize.html");