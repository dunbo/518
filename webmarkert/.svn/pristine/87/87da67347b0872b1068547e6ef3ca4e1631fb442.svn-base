<?php
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
load_helper("utiltool");

include_once('../model/scanSoft.php');

$re = scanSoft::getResponse360();
$model = new GoModel();
$db_server = 'master';
if(!$re){
	echo 'error:no back result';
	exit();
}
$H = date("H");
$minutes = date("i");
$minutes = floor($minutes / 10);
if(!isset($re['file'])){
	exit('error:no file tags');
}

/*if($_GET['UserKey']!='9d70ef24966514774010f41066d72970') {	//360提供的key检查
	exit('error: not equal to userkey');
}*/

include_once GO_APP_ROOT.DS.'function.php';

if (!isset($re['file'][0])) {
	$re['file'] = array(0=>$re['file']);
}

foreach($re['file'] as $info) {
	$temp = $model->findOne(array('table'=>'sj_soft_scan_result','where'=>array('hash'=>$info['md5'],'provider'=>5)));
	if(!$temp) {	//不存在
		permanentlog('360_soft_not_found.log',"sfid: , hash: {$info['md5']} and provider: 5 not found--".date('Y-m-d H:i:s'));
		exit('error:no result mysql');
	} else {
		$data = array();
		$data['time_rep'] = time();
		if($info['state']==0) continue;	//分析失败
		
		if($info['sign']=='不支持' || $info['sign']=='审核中') {
			permanentlog('360_soft_not_sign.log',"hash: {$info['md5']} sign: {$info['sign']} and provider: 5 not found--".date('Y-m-d H:i:s'));
			continue;
		} else if($info['sign']=='安全') {
			$data['safe'] = 1;
		} else if($info['sign'] == '谨慎' || $info['sign']=='未发现已知恶意') {
			$data['safe'] = 0;
		}else {
			$data['safe'] = 2;
		}

		$data['description'] = mysql_escape_string(json_encode($info));
		
		$data['__user_table'] = 'sj_soft_scan_result';

		$sql = "UPDATE {$data['__user_table']} SET time_rep='{$data['time_rep']}',safe='{$data['safe']}',description='{$data['description']}' WHERE hash='{$info['md5']}' AND provider=5;";

		if($data['safe'] > 1) {
			permanentlog("360_sql_unsafe_scan_{$H}_{$minutes}.sql",$sql);
		} else {
			permanentlog("360_sql_safe_scan_{$H}_{$minutes}.sql",$sql);
		}
	}
}
echo 'ok';
?>
