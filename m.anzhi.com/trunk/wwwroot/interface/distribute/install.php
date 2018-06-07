<?php
include_once('./init.php');
$act = 'install';

$time = time();
$req = decryptArgs($_GET['s']);

$channel = getChannel($req['chl']);
if (empty($channel)) {
	header("HTTP/1.1 403 Forbidden");
	exit();
}

if ($channel['co_type'] == 2 && empty($req['req_id'])) {
	header("HTTP/1.1 403 Forbidden");
	exit();
}

if (empty($req['act']) && $req['act'] != $act) {
	header("HTTP/1.1 403 Forbidden");
	exit();
}

if ((time() - $req['ts']) > 3600) {
	header("HTTP/1.1 403 Forbidden");
	exit();
}
$softid = $req['softid'];
$package = $req['package'];
$req_id = empty($req['req_id']) ? '' : $req['req_id'];
$imei = decryptChannelArgs($_GET['az_imei'], $channel);
$imsi = $_GET['az_imsi'];
$mac = $_GET['az_mac'];

$to_log = array(
	'k' => $act,
	'ts' => $time,
	'req_id' => $req_id,
	'channel_id' => $channel['id'],
	'ip' => onlineip(),
	'package' => $req['package'],
	'softid' => $softid,
	'imei' => $imei,
	'imsi' => $imsi,
	'mac' => $mac,
);
writeLog($to_log);

$ret = array(
	'code' => 0,
	'msg' => 'success',
);
exit(json_encode($ret));
