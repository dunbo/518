<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
//session_start();
$chl_code = isset($_GET['chl_code']) ? trim($_GET['chl_code']) : '';
$from = isset($_GET['from']) ? trim($_GET['from']) : '';

$ip = onlineip();
$threshold = 2000;//下载限制

//刷量判断
$is_ip_banned = isIpBanned($ip, md5($chl_code), $threshold);
if ($is_ip_banned) {
	exit('error');
}

//获取相应渠道市场ota最新版
$model = new GoModel();
$option = array(
	'table' => 'sj_channel',
	'where' => array(
		'chl_cid' => $chl_code,
		'status' => 1,
	),
	'cache_time' => 600,
);
$channel = $model->findOne($option);
$cid = $channel['cid'];
$alone_update = $channel['alone_update'];
if ($alone_update == 0) {
	$cid = 0;
}

$option = array(
	'table' => 'sj_market',
	'where' => array(
		'status' => 1,
		'cid' => $cid,
	),
	'order' => 'version_code desc',
	'cache_time' => 600,
);
$market = $model->findOne($option);

$dltime = time();

$tolog = array(
	'key' => 'dl_market',
	'id' => $market['id'],
	'submit_tm' => $dltime,
	'version_code'=> $market['version_code'],
	'ip' => $ip,
	'channel' => $channel['chl'],
	'from' => $from,
	'refer' => $_SERVER['HTTP_REFERER']
);
permanentlog('other.log', json_encode($tolog));

toLocation(getApkHost() . $market['apkurl']);
