<?php
$url = $_GET['url'];
$query = parse_url($url,  PHP_URL_QUERY );
parse_str($query, $arr);
if (isset($arr['anzhiid'])) {
	session_id($arr['anzhiid']);
}
session_start();

if (!$_SESSION['USER_IMEI']) {
	$redis = new Redis();
	$redis->connect('172.16.1.193', 6379);
	$imei = $redis->randomkey();
	session_regenerate_id();
	$arr['imei'] = $imei;
	$arr['anzhiid'] = session_id();
	$url = 'http://m.baidu.com/api?'. http_build_query($arr);
}
header('Location:'. $url);
