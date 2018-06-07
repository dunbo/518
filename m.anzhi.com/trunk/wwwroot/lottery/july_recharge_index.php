<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
session_begin();
if($_GET['end']==1){
    $tplObj -> out['static_url'] = $configs['static_url'];
    $tplObj -> display("july_recharge_end.html");
    exit(0);
}

$aid =264;

$imsi = $_SESSION['USER_IMSI'];
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
permanentlog('activity_'.$aid.'.log', json_encode($log_data));




$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('july_recharge_index.html');
