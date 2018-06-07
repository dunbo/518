<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$aid=$_GET['aid'];
$key_actsid=get_key();
if($key_actsid)
{
	$info= $redis -> get($key_actsid);
	$info['get_rules']=1;
	$info['url']="http://118.26.203.23/lottery/christmas_share.php";
	$save_info=$redis -> set($key_actsid,$info);
}
$tplObj -> out['actsid'] = $key_actsid;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display("christmas_rules.html");