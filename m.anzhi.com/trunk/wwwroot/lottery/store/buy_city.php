<?php
include_once ('./init.php');
session_begin();
$imei= $_SESSION['DEVICEID'];
if(empty($imei)){
    echo '页面已失效，请退出活动重新进入';
    exit(0);
}

$tplObj -> out['static_url'] = $configs['static_url'];

$tpl = "lottery/store/buy_city.html";
$activity_option = array(
	'where' => array(
		'pid' => 0,
		'status' => 1,
	),
	'cache_time' => 3600,
	'table' => 'store_school'
);
$city = $model -> findAll($activity_option,'lottery/lottery');

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

$tplObj -> out['mobile'] = $_GET['mobile'];
$tplObj -> out['city'] = $city;
$tplObj -> display($tpl);
