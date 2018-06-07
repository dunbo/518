<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$aid = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$option = array(
	'where' => array(
		'aid' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity_extend'
);

$result = $model -> findOne($option);

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
$key_actsid = get_key($aid);

if($key_actsid){
	$info['is_share'] = 1;
	$save_info = $redis -> set($key_actsid,$info);
}

$tplObj -> out['actsid'] = $key_actsid;
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['page_result'] = $page_result;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['result'] = $result;
$tplObj -> display("coactivity_extend.html");
