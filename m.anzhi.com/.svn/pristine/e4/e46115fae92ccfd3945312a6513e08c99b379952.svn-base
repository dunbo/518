<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');

$activity_log_file = 'activity_superbowlpreview.log';

// 记日志
$log_data = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => time(),
    'key' => 'click_superbowl'
);
permanentlog($activity_log_file, json_encode($log_data));
exit(0);