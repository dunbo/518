<?php
include_once('./init.php');
$act = 'info';

$time = time();
$req = decryptArgs($_GET['s']);

$channel = getChannel($req['chl']);
if (empty($channel)) {
	header("HTTP/1.1 403 Forbidden");
	exit();
}

if (empty($req['act']) || $req['act'] != $act) {
	header("HTTP/1.1 403 Forbidden");
	exit();
}

if ((time() - $req['ts']) > 3600) {
	header("HTTP/1.1 403 Forbidden");
	exit();
}
$softid = $req['softid'];
$package = $req['package'];
$req_id = generateReqId();
$imei = decryptChannelArgs($_GET['az_imei'], $channel);
$imsi = $_GET['az_imsi'];
$mac = $_GET['az_mac'];

$request = array(
	'q' => mt_rand(0,9999999), //随机值
	'softid' => $softid,
	'package' => $package,
	'ts' => time(),
	'chl' => $req['chl'],
	'req_id' => $req_id,
);
$download_request = $request;
$download_request['act'] = 'download';
ksort($download_request);
$download_url = 'http://' . $_SERVER['HTTP_HOST'] . '/interface/distribute/download.php?s=' . urlencode(encryptArgs($download_request));
$complete_request = $request;
$complete_request['act'] = 'complete';
ksort($complete_request);
$complete_url = 'http://' . $_SERVER['HTTP_HOST'] . '/interface/distribute/complete.php?s=' . urlencode(encryptArgs($complete_request));
$install_request = $request;
$install_request['act'] = 'install';
ksort($install_request);
$install_url = 'http://' . $_SERVER['HTTP_HOST'] . '/interface/distribute/install.php?s=' . urlencode(encryptArgs($install_request));

$to_log = array(
	'k' => $act,
	'ts' => $time,
	'req_id' => $req_id,
	'channel_id' => $channel['id'],
	'ip' => onlineip(),
	'package' => $package,
	'softid' => $softid,
	'imei' => $imei,
	'imsi' => $imsi,
	'mac' => $mac,
);
writeLog($to_log);

$retarr = array(
	'code' => 0,
	'msg' => 'success',
	'data' => array(
		'download_url' => $download_url,
		'complete_url' => $complete_url,
		'install_url' => $install_url,
	),
);
#print_r($retarr);
exit(json_encode($retarr));
