<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
session_begin();
$aid = $_GET['aid'];
$telephone = $_GET['telephone'];
$imsi = $_SESSION['USER_IMSI'];

$log_data = array(
	'imsi' => $imsi,
	'device_id' =>  $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'show_homepage'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

if($_GET['is_sub']==1){

    if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11){
            echo 500;
            exit(0);
    }

    $log_data = array(
            'imsi' => $imsi,
            'device_id' => $_SESSION['DEVICEID'],
            'activity_id' => $_GET['aid'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'sid' => $_GET['sid'],
            'time' => time(),
            'users' => '',
            'uid' => '',
            'telphone' => $telephone,
            'key' => 'telphone'
    );
    permanentlog('activity_'.$_GET['aid'].'.log', json_encode($log_data));
    echo 200;exit(0);
}


$tplObj -> out['aid'] = $_GET['aid'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('pre_download_msg.html');
