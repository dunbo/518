<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();

$package = $_GET['package'];
$aid = $_GET['aid'];
$imei = $_SESSION['USER_IMEI'];
$the_package = "set_like:package_{$aid}";
$all_package = $redis -> gethash($the_package);
if(!$all_package[$imei]){
	$all_package[$imei] = 1;
	$redis -> sethash($the_package,$all_package);
}
$the_telephone = "set_like:telephone_{$aid}";
$all_telephone = $redis -> gethash($the_telephone);
if($all_telephone[$imei]){
	$is_telephone = 1;
}else{
	$is_telephone = 2;
}
echo $is_telephone;
return $is_telephone;

