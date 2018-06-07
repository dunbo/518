<?php
exit('access denify');
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');

//include_once ("../stdafx.php");
load_helper("utiltool");
include_once('../model/scanSoft.php');
$db_server = 'master';
$re = scanSoft::getResponseWQ();
$model = new GoModel();
$H = date("H");
$minutes = date('i');
$minutes = floor($minutes/10);
if(empty($re)){
	echo "error:no get response data";
	exit();
}

$result = $re;
$hash = $_POST['sha1'];
if(isset($result['ScanInfo']['errcode']) && in_array($result['ScanInfo']['errcode'],array("1001","1002"))){
	$option = array(
		'table' => 'sj_soft_scan_result',
		'where' => array(
			'hash' => $hash,
			'provider' => 3,
		),
		'field' => 'sfid',
	);
	$err_list = $model -> findAll($option,$db_server);
	foreach($err_list as $info){
	   $option = array(
		'where' => array(
			'id' => $info['sfid'],
		),
		'field' => 'url,sha1_file',
		'table' => 'sj_soft_file'
	   );
	   $result = $model -> findOne($option,$db_server);
		$params = array(
			"download_url" => load_config('full_static_host').$result['url'],
			"soft_hash" => $result['sha1_file'],
		);
	   $re = scanSoft::requestGetWQ($params);
	}
		exit;
}
$re = $re['pkgInfo'];



# 更新到新表
if(!$hash) 	exit("sha1 is empty");
$data = array(
	'hash' => $hash,
	'provider' => 3,
);
$temp = $model->findOne(
	array(
		'table' => 'sj_soft_scan_result',
		'where' => $data,
	)
	,$db_server
);
$id = 0;
$exists = false;
if($temp) $exists = true;
if (!$exists){
	permanentlog('wq_soft_not_found.log',"sha1: {$hash} not found--".date('Y-m-d H:i:s'));
	exit("sha1: {$hash} not found");
}else{
	$data = array();
	$data['time_rep'] = time();
	$safe = $result['ScanInfo']['responseInfo']['security'];
	$status = ($safe == 'safe') ? 1 : 2;
	$data['safe'] = $status;
	$data['description'] = json_encode($result);
	$data['__user_table'] = 'sj_soft_scan_result';
	$sql = 'update '.$data['__user_table']." set description ='".$data['description']."', safe =".$data['safe'].', time_rep='.$data['time_rep']." where hash ='".$hash."' and provider = 3;";
	if($data['safe'] > 1){ //当天不安全软件数量超100 and为不安全软件 写日志
		permanentlog("wq_sql_unsafe_scan_{$H}_{$minutes}.sql",$sql);
	}else{
		permanentlog("wq_sql_safe_scan_{$H}_{$minutes}.sql",$sql);
	}
}
