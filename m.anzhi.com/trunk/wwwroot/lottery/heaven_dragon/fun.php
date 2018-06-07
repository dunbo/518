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
		$active_id = 467;
	}else{
		$active_id = 476;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/heaven_dragon/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}

if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/heaven_dragon/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
