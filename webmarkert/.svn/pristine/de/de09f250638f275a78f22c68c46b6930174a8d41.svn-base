<?php
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
//include_once ("../stdafx.php");
load_helper("utiltool");
include_once('../model/scanSoft.php');

function mylog($str) {
    $logtime = date("Y-m-d H:i:s", time());
    file_put_contents('/tmp/'. basename(__FILE__). '.log', "${logtime}: ${str}\n", FILE_APPEND);
}

$re = scanSoft::getResponseAQGJ();

if(empty($re)){
	echo '{"code":1, "message":"failure"}';
	exit();
}
$H = date("H");
$minutes = date("i");
$minutes = floor($minutes / 10);
$model = new GoModel();
//$re = $re['app'];
$hash = $re['sha1'];
if(!$hash){
	permanentlog('aqgj_soft_not_found.log',"hash : {$hash} not found!");
	exit('{"code":1, "message":"failure"}');
}
# 更新到新表
$data = array(
	'hash' => $hash,
	'provider' => 2,
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
if($temp)  $exists = true;
if (!$exists){
	permanentlog('aqgj_soft_not_found.log',"hash : {$hash} not found!");
	exit("hash : {$hash} not found in db!");
}else{
	$data = array();
	$data['time_rep'] = time();
	# -1 = 未知 0 = 安全 123不安全
	$data['safe'] = $re['level'] == -1 ? 0: ($re['level'] + 1);
	$data['description'] = json_encode($re);
	$data['__user_table'] = 'sj_soft_scan_result';
	$sql = 'update '.$data['__user_table']." set description ='".mysql_escape_string($data['description'])."', safe =".$data['safe'].',time_rep = UNIX_TIMESTAMP(NOW()) '." where hash ='".$hash."' and provider = 2;";
	if($data['safe'] > 1){ //当天不安全软件数量超100 and为不安全软件 写日志
		permanentlog("aqgj_sql_unsafe_scan_{$H}_{$minutes}.sql",$sql);
	}else{
		permanentlog("aqgj_sql_safe_scan_{$H}_{$minutes}.sql",$sql);
	}
	
	if($temp['is_tmp']){
		$file_table_name = 'sj_soft_file_tmp';
	} else{
		$file_table_name = 'sj_soft_file';
	}	
	
	//给soft_file添加广告信息
/*	if(!empty($re['leafletname']) && !empty($re['leafletaction'])){
		$leaflet = array('leafletname' => $re['leafletname'],'leafletaction' => $re['leafletaction']);		
        $leaflet = json_encode($leaflet);
        $sql = "UPDATE {$file_table_name} SET `leaflet`='1',`leafletname`='".mysql_escape_string($leaflet)."',leafletcnt = leafletcnt | ".AD_AQGJ_M." where `sha1_file`='{$hash}';";
        permanentlog("aqgj_sql_unsafe_scan_{$H}_{$minutes}.sql",$sql);
	}else{
		$sql = "UPDATE {$file_table_name} SET leafletcnt = leafletcnt | ".AD_AQGJ_M." where `sha1_file`='{$hash}';";
        permanentlog("aqgj_sql_safe_scan_{$H}_{$minutes}.sql",$sql);
	}*/

	//给pu_config配置表添加r软件广告配置
	$ad_option = array(
		'where' => array(
			'config_type' => 'AD_SOFT_SHOW',
			'status' => 1
		),
		'field' => 'configcontent',
		'table' => 'pu_config'
	);
	$ad_result = $model -> findOne($ad_option);
	if($ad_result){
		$ad_old = json_decode($ad_result['configcontent'],true);
		$ad_new = explode(',',$re['leafletname']);
		foreach($ad_new as $key => $val){
			if(!in_array($val,$ad_old) && $val){
				$num = count($ad_old);
				$numkey = pow(2, $num);
				$numkey = $numkey;
				if(!empty($val)){
					$ad_old[$numkey] = $val;
				}
			}
		}
		$ad_need = json_encode($ad_old);
		$ad_data = array(
			'configcontent' => $ad_need,
			'__user_table' => 'pu_config'
		);
		$ad_where = array('config_type' => 'AD_SOFT_SHOW','status' => 1);
		if($ad_need != $ad_result['configcontent']){
			$ad_affect = $model -> update($ad_where,$ad_data);
		}
	}else{
		$ad_new = json_encode($re['leafletname']);
		$ad_data = array(
			'configcontent' => $ad_new,
			'config_type' => 'AD_SOFT_SHOW',
			'status' => 1,
			'configname' => '软件广告',
			'__user_table' => 'pu_config'
		);
		$ad_affect = $model -> insert($ad_data);
	}

}
echo '{"code":0, "message":"success"}';
