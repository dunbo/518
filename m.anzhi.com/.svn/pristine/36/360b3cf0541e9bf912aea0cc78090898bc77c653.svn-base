<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}

session_start();

if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$alone_update = $_SESSION['alone_update'];
if($alone_update == 1 && $_SESSION['VERSION_CODE'] < 5410){
	$channel_option = array(
		'where' => array(
			'cid' => $cid,
			'status' => 1,
			'version_code' => array('exp','>=5410'),
			'limit_rules' => array('exp'," ='' or limit_rules is null "),
		),
		'cache_time' => 3600,
		'table' => 'sj_market',
	);
	$channel_result = $model -> findAll($channel_option);
	if(!$channel_result){
		$channel_status = 100;
	}else{
		$channel_status = 200;	
	}
	$tplObj -> out['channel_status'] = $channel_status;
}

if($imsi){
	if($_SESSION['alone_update']==0 && $_SESSION['VERSION_CODE'] < 5410){
		$version_status = 200;
		$intro_option = array(
			'where' => array(
				'package' => 'cn.goapk.market'
			),
			'field' => 'softid,softname,version_code',
			'order' => 'softid DESC',
			'limit' => 1,
			'cache_time' => 86400,
			'table' => 'sj_soft'
		);
		$intro_result = $model -> findOne($intro_option);

		$intro_size_option = array(
			'where' => array(
				'softid' => $intro_result['softid']
			),
			'field' => 'filesize',
			'table' => 'sj_soft_file',
			'cache_time' => 86400
		);
		$intro_size_result = $model -> findOne($intro_size_option);
		$intro_result['soft_sizes'] = formatFileSize('',$intro_size_result['filesize']);
		$intro_result['soft_size'] = $intro_size_result['filesize'];
	}else{
		$version_status = 1000;
	}
	$tplObj -> out['version_status'] = $version_status;
	$tplObj -> out['intro_result'] = $intro_result;
}


$share = $_GET['share'];
$tplObj -> out['share'] = $share;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> display("nosmog_index.html");
