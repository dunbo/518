<?php
/*
** 活动页面上点击下载按钮记日志
*/
require_once(dirname(realpath(__FILE__)) . '/init.php');

$pkgname = $_POST['pkgname'];
$softid = $_POST['softid'];

// 记日志
$log_data = array(
    'imsi' => $_SESSION['USER_IMSI'],
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
	'package' => $pkgname,
	'softid' => $softid,
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'download_soft'
);
permanentlog($activity_log_file, json_encode($log_data));

exit;