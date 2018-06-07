<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();
$aid = $_GET['aid'];
$sid = $_GET['sid'];
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['aid'] = $aid;

session_begin();
$version_code = $_SESSION['VERSION_CODE'];
$alone_update = $_SESSION['alone_update'];
$imsi = $_SESSION['USER_IMSI'];
if(!$imsi || $imsi == '000000000000000'){
	$tplObj -> display('catch_anzhi/summer_coming/sim_no.html');
    exit;
}

//小于6.2版本 升级
if($version_code <6000) 
{
	$soft_model = load_model('softlist');
	$anzhilist = $soft_model->getPackageToSoftId("cn.goapk.market");
	$anzhiid = $anzhilist[0];
	$soft_info = $soft_model ->getsoftinfos($anzhiid, getFilterOption());
	$tplObj -> out['alone_update'] = $alone_update;
	$tplObj -> out['soft_info'] = $soft_info[$anzhiid];
	$tplObj -> display('catch_anzhi/summer_coming/upgrade.html');
}
else
{
	//打开页面日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'users' =>$_SESSION['USER_NAME'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'activity_id' => $aid,
		'sid' => $sid,
		'time' => time(),
		'key' => 'show_homepage',
	);
	permanentlog('activity_'.$aid.'.log', json_encode($log_data));		
	$tplObj -> display('catch_anzhi/summer_coming/index.html');
}
?>
