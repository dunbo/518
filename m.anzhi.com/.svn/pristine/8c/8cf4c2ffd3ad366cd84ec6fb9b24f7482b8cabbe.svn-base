<?php
/*
** 活动页面上点击下载按钮记日志
*/
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$pkgname = $_POST['pkgname'];
$softid = $_POST['softid'];
$type = $_POST['type'];

if( $type ==1  ) {
	$key = 'download_soft';
}else if( $type ==2 ) {
	$key = 'install_soft';
}else if( $type == 3 ) {
	$key = 'open_soft';
}
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
    'key' => $key,
);
permanentlog($activity_log_file, json_encode($log_data));

exit;