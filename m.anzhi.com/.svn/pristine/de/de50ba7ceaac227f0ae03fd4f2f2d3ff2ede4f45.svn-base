<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/xy2/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
if($_SESSION['VERSION_CODE'] < 6000){
	$soft_model = load_model('softlist');
	$anzhilist = $soft_model->getPackageToSoftId("cn.goapk.market");
	//$anzhiid = $anzhilist[0];
	$anzhiid = array_pop($anzhilist);
	$soft_info = $soft_model ->getsoftinfos($anzhiid, getFilterOption());
	$tplObj -> out['alone_update'] = $alone_update;
	$tplObj -> out['soft_info'] = $soft_info[$anzhiid];
}
if($stop == 1){
	$tpl = "lottery/".$prefix."/end.html";	
}else{
	$tpl = "lottery/".$prefix."/index.html";	
}
$tplObj -> out['imsi'] = $imsi;
$tplObj -> out['lottery_prize']  =  get_lottery_prize();	
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> display($tpl);