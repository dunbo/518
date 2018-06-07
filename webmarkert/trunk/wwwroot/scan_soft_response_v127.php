<?php
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
load_helper("utiltool");

include_once('../model/scanSoft.php');

$re = scanSoft::getResponseQQ_v127();
$model = new GoModel();
$db_server = 'master';
if(!$re){
	echo "error:no get response data";
	exit();
}
function mylog($str) {
    $logtime = date("Y-m-d H:i:s", time());
    file_put_contents('/tmp/'. basename(__FILE__). '.log', "${logtime}: ${str}\n", FILE_APPEND);
}
$H = date("H");
$minutes = date("i");
$minutes = floor($minutes / 10);
if(!isset($re['resultlist'])){
	exit("invoke data!!");
}

include_once GO_APP_ROOT.DS.'function.php';


$scanlist = $re['resultlist'];
foreach($scanlist as $info){
	$sfid = $info['sid'];
	$hash = $info['md5'];
	$safetype = $info['safetype'];
/* 
virusname	��������, utf8����	��ѡ���ǲ���ʱֵΪ��
virusdesc	����������utf8����	��ѡ���ǲ���ʱֵΪ��
notifybar	֪ͨ�����������ǣ��ֱ�Ϊ-1-��ȷ����0-��1-��	��ѡ
integralwall	����ǽ���������ǣ��ֱ�Ϊ-1-��ȷ����0-��1-��	��ѡ
official	�ٷ�������ǣ��ֱ�Ϊ-1-��ȷ����0-��1-��	��ѡ
*/
	$virusname = $info['virusname'];
	$virusdesc = $info['virusdesc'];
	$notifybar = $info['notifybar'];
	$integralwall = $info['integralwall'];
	$official = $info['official'];
	# ���µ��±�

	$data = array(
		//'sfid' => $sfid,
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
		$data['safe'] = $safetype;//1Ϊ��ȫ 2Ϊ���� 3Ϊ�жȷ��� 4Ϊ�Ͷȷ���
		$data['description'] = 	json_encode(
			array(
				//'sfid' => $sfid,
				'virusname' => $info['virusname'],
				'virusdesc' => $info['virusdesc'],
				'notifybar' => $info['notifybar'],
				'integralwall' => $info['integralwall'],
				'official' => $info['official']
			)
		);
		$data['__user_table'] = 'sj_soft_scan_result';
		//
		if($temp['is_tmp']){
			$file_table_name = 'sj_soft_file_tmp';
		} else{
			$file_table_name = 'sj_soft_file';
		}
		//�����
		if($info['notifybar'] == 1 || $info['integralwall'] == 1){
			$ad_sql = "update {$file_table_name} set leaflet = 1 ,leafletcnt = leafletcnt | ".AD_QQ_M." where md5_file = '{$hash}';";
			permanentlog("qq_sql_unsafe_scan_v127_{$H}_{$minutes}.sql",$ad_sql);
		}else{
			$ad_sql = "update {$file_table_name} set leafletcnt = leafletcnt | ".AD_QQ_M." where md5_file = '{$hash}';";
			permanentlog("qq_sql_safe_scan_v127_{$H}_{$minutes}.sql",$ad_sql);
		}
		
		$sql = 'update '.$data['__user_table']." set description ='".mysql_escape_string($data['description'])."', safe =".$data['safe'].', time_rep=UNIX_TIMESTAMP(NOW())'." where  hash ='".$hash."' and provider = 1;";
		if($data['safe'] > 1){ //���첻��ȫ����������100 andΪ����ȫ���� д��־
			permanentlog("qq_sql_unsafe_scan_v127_{$H}_{$minutes}.sql",$sql);
		}else{
			permanentlog("qq_sql_safe_scan_v127_{$H}_{$minutes}.sql",$sql);
		}
	}
}
echo '{"result":"ok"}';
?>