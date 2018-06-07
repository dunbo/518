<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

$prefix_url = 'promotion.anzhi.com';

$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['imgurl'] = getImageHost();
