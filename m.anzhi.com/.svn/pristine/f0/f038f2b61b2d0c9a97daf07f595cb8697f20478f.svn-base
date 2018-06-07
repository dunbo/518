<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 179;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}
session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$share_id = $_GET['share_id'];

$share_num = "christmas_{$active_id}:share_{$share_id}";
$new_num = $redis -> setx('incr',$share_num,0);
if($new_num){
	$new_num = $redis -> setx('incr',$share_num,1);
}else{
	if($share_id == 1){
		$start_num = 54371;
	}else if($share_id == 2){
		$start_num = 36001;
	}else if($share_id == 3){
		$start_num = 12987;
	}else if($share_id == 4){
		$start_num = 9854;
	}else if($share_id == 5){
		$start_num = 10076;
	}else if($share_id == 6){
		$start_num = 8701;
	}else if($share_id == 7){
		$start_num = 9043;
	}else if($share_id == 8){
		$start_num = 7654;
	}else if($share_id == 9){
		$start_num = 12093;
	}else if($share_id == 10){
		$start_num = 2903;
	}else if($share_id == 11){
		$start_num = 26731;
	}else if($share_id == 12){
		$start_num = 34672;
	}
	$new_num = $redis -> setx('incr',$share_num,$start_num);
}

echo $new_num;
return $new_num;
















