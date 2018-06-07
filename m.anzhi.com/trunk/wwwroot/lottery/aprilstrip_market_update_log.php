<?php
/*
** 点击市场升级的统计接口
*/

include_once (dirname(realpath(__FILE__)).'/aprilstrip_init.php');

if (!$imsi_status) {
	exit;
}

$package = $_POST['pkgname'];
if ($package != 'cn.goapk.market') {
	exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $sid,
    'time' => time(),
    'package' => $package,
    'key' => 'aprilstrip_market_update_log'
);
permanentlog($activity_log_file, json_encode($log_data));

exit;