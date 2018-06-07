<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if(!isset($active_id)){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$active_id = 492;
	}else{
		$active_id = 503;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/dark_dawn2_reserve/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}

if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$_GET['stop']=1;
	}
}
