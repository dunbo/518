<?php
include_once ('./init.php');
session_begin();
$imei= $_SESSION['DEVICEID'];

$tplObj -> out['static_url'] = $configs['static_url'];

$activity_option = array(
	'where' => array(
		'id' => 1
	),
	'cache_time' => 3600,
	'table' => 'store_config'
);
$config_info = $model -> findOne($activity_option,'lottery/lottery');
$config_info['explain'] = str_replace("\n","<br/>",$config_info['explain']);
$tplObj -> out['config_info'] = $config_info;

$tpl = "lottery/store/buy_end.html";

$tplObj -> display($tpl);
