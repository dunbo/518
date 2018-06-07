<?php
//exit('{"error_code":1}');
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
load_helper("utiltool");

include_once('../model/scanSoft.php');

$re = scanSoft::getResponseJS();
$model = new GoModel();
$db_server = 'master';
if(!$re){
	echo '{"error_code":3}';
	exit();
}
$H = date("H");
$minutes = date("i");
$minutes = floor($minutes / 10);
if(!isset($re['scanResult'])){
	exit('{"error_code":2}');
}

include_once GO_APP_ROOT.DS.'function.php';


$scanlist = json_decode(str_replace('\\','',$re['scanResult']),true);
foreach($scanlist['resultList'] as $info){
	$hash = $info['md5'];
	$data = array(
		'hash' => $info['md5'],
		'provider' => 4,
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
	$sfid = $temp['sfid'];
	if($temp) $exists = true;
	if(!$exists){
		permanentlog('js_soft_not_found.log',"sfid : {$sfid} ,hash : {$hash} and provider : 1 not found--".date('Y-m-d H:i:s'));
		exit('{"error_code":1}');		
	}else{
		$data = array();
		$data['time_rep'] = time();
		if($info['safe_type'] == 0) continue;
		if($info['safe_type'] == 2){ //安全
			$safe_type = 1;
		}else{  
			$safe_type = 2;
		} 
		$data['safe'] = $safe_type;
		$data['description'] = 	json_encode($info);
		$data['__user_table'] = 'sj_soft_scan_result';
		
		if($temp['is_tmp']){
			$file_table_name = 'sj_soft_file_tmp';
		} else{
			$file_table_name = 'sj_soft_file';
		}
		
/* 		if(count($info['adinfo']) > 0 ){
			 $ad_sql = "update {$file_table_name} set leaflet = 1,leafletcnt = leafletcnt | ".AD_JS_M."  where id = {$sfid};"; 
			 permanentlog("js_sql_unsafe_scan_{$H}_{$minutes}.sql",$ad_sql);
		}else{
			 $ad_sql = "update {$file_table_name} set leafletcnt = leafletcnt | ".AD_JS_M."  where id = {$sfid};"; 
			 permanentlog("js_sql_safe_scan_{$H}_{$minutes}.sql",$ad_sql);		
		} */
		$sql = 'update '.$data['__user_table']." set description ='".mysql_escape_string($data['description'])."', safe =".$data['safe'].', time_rep=UNIX_TIMESTAMP(NOW())'." where hash ='".$hash."' and provider = 4;";
		if($data['safe'] > 1){ //当天不安全软件数量超100 and为不安全软件 写日志
			permanentlog("js_sql_unsafe_scan_{$H}_{$minutes}.sql",$sql);
		}else{
			permanentlog("js_sql_safe_scan_{$H}_{$minutes}.sql",$sql);
		}
	}
}
echo '{"error_code":0}';
?>
