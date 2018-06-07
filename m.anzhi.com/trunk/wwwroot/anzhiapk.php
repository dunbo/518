<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
$model = load_model('softlist');
$anzhilist = $model->getPackageToSoftId("cn.goapk.market");
$anzhiid = $anzhilist[0];
$type = isset($_GET['type']) ? $_GET['type'] : 0;
$tplObj -> out['anzhiid'] = $anzhiid;
if($type){
	$tplObj -> out['type'] = $type;
	$tplObj -> display('downloadapk.html');
}else{
	$tplObj -> display('anzhiapk.html');
}