<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
session_begin();

if($_SESSION['VERSION_CODE'] < 5300){
	$tplObj -> out['channel_status'] = 1000;
}
//top 10
$user_top = $redis -> get("recharge_top:user_top");
$model = new GoModel();

//中奖记录  CACHE
$option = array(
	'table' => 'recharge_top_award',
	'field' => 'username ,prizename',
	'cache_time' => 86400,
);
$awardarr = $model->findAll($option,'lottery/lottery');
foreach($awardarr as $k=>$v)
{
    $username = $v['username'];
    $awardarr[$k]['new_name'] = str_replace(mb_substr($username,2,4,'utf-8'),'XXXX',$username);
}

$tplObj -> out['user_top'] = $user_top;
$tplObj -> out['awardarr'] = $awardarr;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('recharge_top_end.html');
