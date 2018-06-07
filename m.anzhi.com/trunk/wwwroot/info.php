<?php
error_reporting(E_ALL ^ E_NOTICE);
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GOPHP_ROOT', realpath(dirname(realpath(__FILE__))). DS. '..'. DS. 'GoPHP');
define('GO_CONFIG_DIR', GO_APP_ROOT. DS.'..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_MODEL_DIR', GO_APP_ROOT. DS.'..'. DS. 'newgomarket.goapk.com'. DS. 'model');
define('GO_HELPER_DIR', GO_APP_ROOT. DS.'..'. DS. 'newgomarket.goapk.com'. DS. 'helper');

include_once GO_APP_ROOT.DS.'..'. DS.'newgomarket.goapk.com'.DS. 'lib'. DS. 'GoService.class.php';
include_once GO_APP_ROOT. DS.'..'.DS. 'GoPHP'. DS. 'Startup.php';
load_helper('utiltool');
$model = load_model('channelfirst');
$imei = strtolower($_GET['imei']);
$pid = $_GET['pid'];
$type = isset($_GET['type'])?$_GET['type']:1;//1代表imei为imei，2代表imei值是sim卡号

$cp = "";
if($pid==4){//4为平板，所以要使用平板的数据库
	$_REQUEST['USEHC'] = true;
}
if (array_key_exists('cp', $_GET)) $cp = $_GET['cp'];
$info = array(
    'mt' => $_GET['mt'],
    'imei' => $imei,
    'firmware' => $_GET['sdk'],
    'sign' => $_GET['sign'],
    'cp' => $cp,
	'submit_tm' => time()
);
if ($pid == 1 or $pid == 2 or $pid == 4) {
    $info['pid'] = $pid;

    if($pid == 1){
    	$info['type'] = $type;
    	$mac = isset($_GET['mac'])?$_GET['mac']:'';
    	$info['mac'] = $mac;

    	if($type==2){
    		$ssn = isset($_GET['ssn'])?$_GET['ssn']:'';
    		$imsi = isset($_GET['imsi'])?$_GET['imsi']:'';
    		$info['imsi'] = $imsi;
    		$info['r_imei'] = $imei;
    		$info['imei'] = $imsi;
    		$info['ssn'] = $ssn;
    	}

    }

	$option = array(
		'where' => array(
			'imei' => $info['imei'],
			'pid' => $pid
		),

		'field' => 'count(*) AS counts'
	);
	$res = $model->findOne($option);
	$exists = $res['counts'];

	$option = array(
		'where' => array(
			'chl' => $_GET['chl'],
		),
		'table' => 'sj_channel',
		'field' => 'cid'
	);
	$res = $model->findOne($option);
	$info['cid'] = $res['cid'];
    if ($exists == 0) {
    	$info['status'] = 0;//验证接口获得信息直接存入数据库

    	$sign = $info['sign'];

    	if($sign == md5($info['imei'] . '@!goapk#$')
    	||$sign == md5($info['chl'] . '@!goapk#$')
    	||$sign == md5(strtolower($info['imei']) . '@!goapk#$')
    	||$sign == md5(strtolower($info['chl']) . '@!goapk#$')
    	||$sign == md5(strtoupper($info['imei']) . '@!goapk#$')
    	||$sign == md5(strtoupper($info['chl']) . '@!goapk#$')){
    		$info['status'] = 0;
    	}else{
    		$info['status'] = 2;
    	}

    	$log_data = array("date"=>time(),"info"=>$info);
    	permanentlog("channel_first.info", json_encode($log_data));

        $model->insert($info);
    }
    $info["__user_action"] = "REPLACE";
    $info["__user_table"] = "pu_channel_last";

    $model->insert($info);

} else if ($pid == 3) {
	/*
    $info['model'] = $_GET['model'];
    $exists = $softObj->getDataList('sdbackup_source', array(
                    'where' => array( 'imei' => $imei)
                ));
    if (empty($exists)) {
        $softObj->addToTable('sdbackup_source', $info);
    }
    */
}

