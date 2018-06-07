<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
header('content-type:text/html;charset=utf-8');
$dltime = time();
$hour = date('H');
$ip = onlineip();
$model = new GoModel();

//判断cid是否存在
if($_GET['cid']){
	$option = array(
		'table' => 'sj_channel',
		'where' => array(
			'status' => 1,
			'cid' => $_GET['cid'],
		),
		'field' => 'cid,alone_update',
	);
	$channel = $model->findOne($option);
	//判断是否独立
	if (!($channel['alone_update']==1) && isset($channel)) {
		$option = array(
			'table' => 'sj_market',
			'where' => array(
				'status' => 1,
				'cid' => $channel['cid'],
			),
			'order' => 'version_code desc'
		);
		$market = $model->findOne($option);
	}else{
		$option = array(
			'table' => 'sj_market',
			'where' => array(
				'status' => 1,
				'cid' => 0,
			),
			'order' => 'version_code desc'
		);
		$market = $model->findOne($option);
	}
}else{
	$option = array(
		'table' => 'sj_market',
		'where' => array(
			'status' => 1,
			'cid' => 0,
		),
		'order' => 'version_code desc'
	);
	$market = $model->findOne($option);
}

//生成日志
if ($market['apkurl']) {
	permanentlog('market_' . $hour . '.json', json_encode(array(
		'action' => "link",
		'dl_time' => $dltime,
		'type' => "market",
		'apk' => $market['apkurl'],
		'ip' => $ip,
		'cid' => $_GET['cid'] ? $_GET['cid'] : "0",
		'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '',
	)));

	toLocation(getApkHost() . $market['apkurl']);
}
// toLocation("http://m.goapk.com");