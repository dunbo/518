<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

// 记日志
$log_data = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => time(),
	'softid' => $_GET['softid'],
	'package' => $_GET['package'],
    'key' => 'download_click'
);
permanentlog($activity_log_file, json_encode($log_data));

exit(0);