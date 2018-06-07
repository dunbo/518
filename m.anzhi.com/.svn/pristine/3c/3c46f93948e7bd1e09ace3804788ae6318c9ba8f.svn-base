<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$aid = 294;
$prize_redis = "schoolseason_lottery:gift_{$aid}";
$option = array(
	'where' => array(
		'status' => 1
	),
	'table' => 'schoolseason_gift_num'
);
$result = $model -> findAll($option,'lottery/lottery');
foreach($result as $k => $v){
	$gift_num_arr[] = $v['gift_num'];
}
$redis -> setlist($prize_redis,$gift_num_arr);
