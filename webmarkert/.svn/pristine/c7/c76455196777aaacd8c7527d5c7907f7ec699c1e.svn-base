<?php
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
load_helper("utiltool");

include_once('../model/scanSoft.php');

$re = scanSoft::getResponseQQ_v130();
$model = new GoModel();
$db_server = 'master';
if(!$re || $re['result'] == 'error'){
	exit(json_encode($re));
}
function mylog($str) {
    $logtime = date("Y-m-d H:i:s", time());
    file_put_contents('/tmp/'. basename(__FILE__). '.log', "${logtime}: ${str}\n", FILE_APPEND);
}
$H = date("H");
$minutes = date("i");
$minutes = floor($minutes / 10);
if(!isset($re['resultlist'])){
	exit(json_encode($re));
}

include_once GO_APP_ROOT.DS.'function.php';


$scanlist = $re['resultlist'];
foreach($scanlist as $info){
	$sfid = $info['sid'];
	$hash = $info['md5'];
	$safetype = $info['safetype'];
/* 
virusname	病毒名称, utf8编码	必选，非病毒时值为空
virusdesc	病毒描述，utf8编码	必选，非病毒时值为空
notifybar	通知栏广告软件标记，分别为-1-不确定，0-否，1-是	必选
integralwall	积分墙广告软件标记，分别为-1-不确定，0-否，1-是	必选
official	官方软件标记，分别为-1-不确定，0-否，1-是	必选
*/
	$virusname = $info['virusname'];
	$virusdesc = $info['virusdesc'];
	$notifybar = $info['notifybar'];
	$integralwall = $info['integralwall'];
	$official = $info['official'];
	# 更新到新表

	$data = array(
		'hash' => $hash,
		'provider' => 1,
	);
	$temp = $model->findOne(
		array(
			'table' => 'sj_soft_scan_result',
			'where' => $data,
		),
		$db_server
	);
	$id = 0;
	$exists = false;
	if($temp) $exists = true;
	if(!$exists){
		permanentlog('qq_soft_not_found.log',"sfid : {$sfid} ,hash : {$hash} and provider : 1 not found--".date('Y-m-d H:i:s'));
		exit('{"result":"ok"}');		
	}else{
		$data = array();
		$data['time_rep'] = time();
		$data['safe'] = $safetype;//1为安全 2为病毒 3为中度风险 4为低度风险
		$data['description'] = 	json_encode(
			array(
				'sfid' => $sfid,
				'virusname' => $info['virusname'],
				'virusdesc' => $info['virusdesc'],
				'notifybar' => $info['notifybar'],
				'integralwall' => $info['integralwall'],
	//			'official' => $info['official'],
				'banner' => $info['banner'],
				'floatwindowns' => $info['floatwindows'],
				'spot' => $info['spot'],
				'boutiquerecommand' => $info['boutiquerecommand'],
				'pluginlist' => $info['pluginlist']
			)
		);
		$data['__user_table'] = 'sj_soft_scan_result';
		$sql = 'update '.$data['__user_table']." set description ='".mysql_escape_string($data['description'])."', safe =".$data['safe'].', time_rep=UNIX_TIMESTAMP(NOW())'." where  hash ='".$hash."' and provider = 1;";
		if($data['safe'] > 1){ //当天不安全软件数量超100 and为不安全软件 写日志
			permanentlog("qq_sql_unsafe_scan_v127_{$H}_{$minutes}.sql",$sql);
		}else{
			permanentlog("qq_sql_safe_scan_v127_{$H}_{$minutes}.sql",$sql);
		}
	}
}
echo '{"result":"ok"}';
?>
