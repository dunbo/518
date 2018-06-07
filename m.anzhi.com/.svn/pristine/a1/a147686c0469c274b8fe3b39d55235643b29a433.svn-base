<?php
include(dirname(realpath(__FILE__)).'/init.php');
$ip = onlineip();
$threshold = 2000;//下载限制

if (empty($_GET['softid'])) {
	exit;
}
$softid = $_GET['softid'];

$tplObj->out['softid'] = $softid;
$tplObj->out['msg'] = "您当前对这个软件的下载操作过于频繁，被视为恶意刷下载量，需要输入安全验证后才能下载。";

list($msec, $sec) = explode(' ', microtime());
$msec = substr($msec, 2);
$tplObj->out['rand'] = $msec;

if (isset($_POST['codedownid'])) {
	go_require_once(dirname(realpath(__FILE__)).'/checkcode/config.php');
	if(formcheck($_POST['codedownid'])==false) {
		$tplObj->out['msg'] = "验证码错误，请重新输入";
		$tplObj->display ( 'yanzhengma.html' );
		exit;
	} else {
		start_download($softid);
		exit;
	}
}

if (isIpBanned($ip, $softid, $threshold)) {
	$tplObj->display ( 'yanzhengma.html');
	exit;
}

start_download($softid);

function start_download($softid) {
	global $softObj, $ip, $chl_code;
	$option = array('softid' => $softid,'status'=>1,'hide'=>array(0,1));
	$app = $softObj->getDataList('sj_soft', array('where' => $option));
	$package = $app[0]['package'];
	if (empty($package)) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	$softfile = $softObj -> getDataList('sj_soft_file', array('where' => array('softid' => $softid,'package_status' => 1)));
	if (!$softfile) {
		$softfiles = $softObj -> getDataList('sj_soft', array('where' => array('package' => $package,'hide' => 0))); //soft history
		uasort($softfiles, create_function('$a, $b', 'return $a["upload_tm"]<$b["upload_tm"]?1:-1;'));
		foreach($softfiles as $info){
			$softids[] = $info['softid'];
		}
		$softfile = $softObj -> getDataList('sj_soft_file',array('where' => array('softid' => $softids[0])));
	}

	$h = date("H");
	$dltime = time();
	$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
	$channel = $_GET['channel'];
	if ( $channel ) {
		$device_mark = isset($_GET['did']) ? $_GET['did'] : '';
		permanentlog('parter_'.$h.'.json', json_encode(array('softid' => $softid,
					'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
					'action' => $channel,
					'did'  => $device_mark,
					'submit_tm' => $dltime,
					'package' => $package,
					'ip' => $ip,
					'channel' => defined('CHL') ? CHL : $chl_code,
					)));
	}

	permanentlog('install_log_'.$h.'.json', json_encode(array('softid' => $softid,
					'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
					'action' => 10,//自己定的下载日志记录号
					'submit_tm' => $dltime,
					'package' => $package,
					'device' => $_SERVER['DEVICE'],
					'channel' => defined('CHL') ? CHL : $chl_code,
					'ip' => $ip
					)));


	$filename = "$package.apk";
	toLocation(getApkHost($softfile[0]) .$softfile[0]['url']);
}
