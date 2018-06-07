<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$aid = 218;

$all_package = array(4=>'com.kokozu.android',5=>'com.kokozu.android',6=>'com.cmge.xianjian.anzhi',7=>'com.achievo.vipshop');
$redis -> delete("superman_lottery:package_{$aid}");
$redis -> sethash("superman_lottery:package_{$aid}",$all_package);
$rr = $redis -> gethash("superman_lottery:package_{$aid}");

$option_4 = array(
	'where' => array(
		'award_level' => 4
	),
	'field' => 'gift_num',
	'table' => 'superman_the_award'
);

$result_4 = $model -> findAll($option_4,'lottery/lottery');
foreach($result_4 as $key => $val){
	$results_4[] = $val['gift_num'];
}

$option_5 = array(
	'where' => array(
		'award_level' => 5
	),
	'field' => 'gift_num',
	'table' => 'superman_the_award'
);

$result_5 = $model -> findAll($option_5,'lottery/lottery');
foreach($result_5 as $key => $val){
	$results_5[] = $val['gift_num'];
}
$option_6 = array(
	'where' => array(
		'award_level' => 6
	),
	'field' => 'gift_num',
	'table' => 'superman_the_award'
);

$result_6 = $model -> findAll($option_6,'lottery/lottery');
foreach($result_6 as $key => $val){
	$results_6[] = $val['gift_num'];
}
$option_7 = array(
	'where' => array(
		'award_level' => 7
	),
	'field' => 'gift_num',
	'table' => 'superman_the_award'
);

$result_7 = $model -> findAll($option_7,'lottery/lottery');
foreach($result_7 as $key => $val){
	$results_7[] = $val['gift_num'];
}
$prize_4 = "superman_lottery:4_{$aid}";
$prize_5 = "superman_lottery:5_{$aid}";
$prize_6 = "superman_lottery:6_{$aid}";
$prize_7 = "superman_lottery:7_{$aid}";
$redis -> delete($prize_4);
$redis -> delete($prize_5);
$redis -> delete($prize_6);
$redis -> delete($prize_7);
$redis -> setlist($prize_4,$results_4);
$redis -> setlist($prize_5,$results_5);
$redis -> setlist($prize_6,$results_6);
$redis -> setlist($prize_7,$results_7);

