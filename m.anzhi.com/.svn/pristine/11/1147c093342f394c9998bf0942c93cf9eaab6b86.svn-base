<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$sid = $_GET['sid'];
$aid = $_GET['aid'];
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$key=get_key();
if($key)
{
	$info= $redis -> get($key);
	$info_arr=json_decode($info, true);
	$info_arr['get_end']=1;
	$save_info=$redis -> set($key,json_encode($info_arr));
}

$tplObj -> out['key'] = $key;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display("superman_end.html");