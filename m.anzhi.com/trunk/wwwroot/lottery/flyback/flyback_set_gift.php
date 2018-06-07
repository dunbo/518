<?php
//礼包缓存
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id =346;
$prize_redis = "flyback_gift_{$active_id}";
$option = array(
	'where' => array(
		'status' => 0
	),
	'table' => 'flyback_gift'
);
$result = $model -> findAll($option,'lottery/lottery');
foreach($result as $k => $v){
	$gift_num_arr[] = array(
		'package' => $v['package'],
		'softname' => $v['softname'],
		'gift_number' => $v['gift_number']
	);
}
// $gift_num_arr = array(
	// '0'=>array(
		// 'package' => 'com.fgol.anzhi',
		// 'softname' => '房贷卡烧烤架反倒是',
		// 'gift_number' => '252X2RR3V8YPJD'
	// )
// );
//var_dump($gift_num_arr);exit();
$redis -> delete($prize_redis);
$redis -> setlist($prize_redis,$gift_num_arr);
var_dump($redis->getlist($prize_redis));

