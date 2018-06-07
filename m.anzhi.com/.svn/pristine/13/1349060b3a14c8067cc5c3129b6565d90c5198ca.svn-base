<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
if (!isset($_GET['chl_cid'])) {
	exit;
}
$chl_cid = $_GET['chl_cid'];
$op = isset($_GET['op']) ? $_GET['op'] : 'search';

$model = new GoModel();
//获取渠道cid
$option = array(
	'table' => 'sj_channel',
	'where' => array(
		'chl_cid' => $chl_cid
	),
	'field' => 'cid,chl',
	'cache_time' => 864000
);
$channel_cid = $model->findOne($option);
if(!empty($channel_cid['cid'])){
	$url_option = array(
		'table' => 'sj_market',
		'where' => array(
			'cid' => $channel_cid['cid'],
			'status' => 1
		),
		'order' => 'id desc',
		'field' => 'id,apkurl,apksize',
		'cache_time' => 1800
	);
	$apk_url = $model->findOne($url_option);
	if($apk_url){
		if ($op == 'search') {
			$log_arr = array(
				'key_word'=>$key_word,
				'chl'=>$channel_cid['chl'],
				'time'=>time(),
				'ip'=>$_SERVER["REMOTE_ADDR"],
				'browser'=>$_SERVER['HTTP_USER_AGENT'],
				'type'=>$_GET['type']
			);
			permanentlog('ad_download_log.json', json_encode($log_arr));			
		}
		
		$apk_url['filesize'] = $apk_url['apksize'];
		toLocation(getApkHost($apk_url) . $apk_url['apkurl']);
		exit;
	}else{
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	
}else{
	header("HTTP/1.1 404 Not Found");
	exit;
}
