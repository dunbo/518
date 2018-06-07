<?php
/*
** 活动首页
*/

include_once (dirname(realpath(__FILE__)).'/octoberflight_init_page.php');

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'show_homepage'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display("octoberflight_index.html");
exit;