<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 185;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_POST['sid'] && eregi('[0-9a-zA-z]', $_POST['sid']) && strlen($_POST['sid']) == 32){
	session_id($_POST['sid']);
}

session_start();
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$package = $_POST['pkgname'];

$my_need = array($imsi,$package);

load_helper('task');
$task_client = get_task_client();
$the_gift = $task_client->do('vacation_lottery',json_encode($my_need));
if($the_gift == 200 || $the_gift == 300){
	$the_gifts = '';
}else{
	$the_gifts = $the_gift;
}
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_POST['sid'],
	'time' => time(),
	'gift' => $the_gifts,
	'package' => $my_need[1],
	'key' => 'download_soft'
);

permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
echo $the_gift;
return $the_gift;







