<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

// 记日志
$log_data = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => time(),
    'key' => 'play'
);
permanentlog($activity_log_file, json_encode($log_data));

$tplObj->display('lottery/defendwar/play.html');

exit;