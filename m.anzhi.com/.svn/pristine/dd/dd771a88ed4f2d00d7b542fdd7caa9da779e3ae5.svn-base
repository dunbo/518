<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
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
	$info_arr['get_rule']=1;
	$save_info=$redis -> set($key,json_encode($info_arr));
}

$tplObj -> out['key'] = $key;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display("superman_rule.html");