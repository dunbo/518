<?php
require_once(dirname(realpath(__FILE__)) . "/init.php");
$log_data = array(
		'imsi' => $imsi,
		'sid' => $sid,
		'device_id' => $_SESSION['DEVICEID'],
		'time' => time(),
		'activity_id' => $aid,
		'key' => 'share_soft'
	);
permanentlog($activity_log_file, json_encode($log_data));
echo 1;