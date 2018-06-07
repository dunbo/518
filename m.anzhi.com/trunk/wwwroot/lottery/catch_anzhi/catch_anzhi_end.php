<?php 
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();
$aid = 369;
$sid = $_GET['sid'];
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['aid'] = $aid;

session_begin();
$version_code = $_SESSION['VERSION_CODE'];
//小于6.0版本 升级
if($version_code <6000)
{
	$soft_model = load_model('softlist');
	$anzhilist = $soft_model->getPackageToSoftId("cn.goapk.market");
	$anzhiid = $anzhilist[0];
	$soft_info = $soft_model ->getsoftinfos($anzhiid, getFilterOption());
	$tplObj -> out['soft_info'] = $soft_info[$anzhiid];
	$from = 1;
	$tplObj -> out['from'] = $from;
	$tplObj -> display('catch_anzhi/upgrade.html');
}
else
{	
	$tplObj -> display('catch_anzhi/end.html');
}

?>