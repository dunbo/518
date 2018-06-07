<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');

$map = include('config.php');

if (!isset($_GET['package']) || !isset($_GET['vcode'])) {
    exit;
}

$package = $_GET['package'];
$vcode = $_GET['vcode'];

$url = $map[$package][$vcode][0];

$tolog = array(
	'action' => 300,//第三方下载数据
	'submit_tm' => time(),
	'package' => $package,
	'ip' => onlineip(),
	'refer' => $_SERVER['HTTP_REFERER'],
    'gcid' => $_GET['vcode']
);
$h = date('H');
permanentlog('install_log_'.$h.'.json', json_encode($tolog));

header("Location:".$url);
exit;
