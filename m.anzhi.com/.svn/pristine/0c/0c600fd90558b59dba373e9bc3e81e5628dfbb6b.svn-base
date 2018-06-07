<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');

$tplObj->out['new_static_url'] = $configs['new_static_url'];

$activity_log_file = 'activity_superbowlpreview.log';

// 记日志
$log_data = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => time(),
    'key' => 'index'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display("lottery/superbowlpreview/index.html");

