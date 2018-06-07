<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$log_file = 'ad_softinfo.log';
$log_data = array(
	'ip' => $_SERVER['REMOTE_ADDR'],
    'time' => time(),
	'softid' => $_POST['softid'],
	'soft_name' => $_POST['soft_name'],
	'pkg' => $_POST['pkg'],
	'chl_cid' => '07309bcc3952',
	'key' => $_POST['key'],
);
permanentlog($log_file, json_encode($log_data));