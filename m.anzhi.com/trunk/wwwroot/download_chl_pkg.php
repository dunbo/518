<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
//session_start();
if (!isset($_GET['softid']) && !isset($_GET['package']) && !isset($_GET['market'])) {
	exit;
}
if (isset($_GET['gcid'])) {
	$_SESSION['GAME_CID'] = intval($_GET['gcid']);
}
$ip = onlineip();
$threshold = 2000;//下载限制

//刷量判断
$key = isset($_GET['softid']) ? $_GET['softid'] : $_GET['package'];
$tplObj->out['issoftid'] = isset($_GET['softid']) ? 1 : 0;
$tplObj->out['key'] = $key;
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

	}
} else {
	$is_ip_banned = isIpBanned($ip, md5($key), $threshold);
	if ($is_ip_banned) {
		$tplObj->display ( 'yanzhengma.html');
		exit;
	}
}
$model = new GoModel();
$option = array(
	'table' => 'sdk_channel_game',
	'where' => array(
		'id' => $_GET['chl_pkg_url_id'],
	),
	'field' => 'url',
);
$result = $model->findOne($option);
$img_host = getImageHost();
$url = $img_host.$result['url'];
$h = date("H");
$tolog = array(
	'softid' => $_GET['softid'],
	'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
	'action' => 10,//自己定的下载日志记录号
	'submit_tm' => time(),
	'package' => $_GET['package'],
	'device' => $_SERVER['DEVICE'],
	'channel' => defined('CHL') ? CHL : $chl_code,
	'ip' => $ip,
	'refer' => $_SERVER['HTTP_REFERER'],
	'sdk_chl_gm_id' => $_GET['chl_pkg_url_id'],//【渠道游戏包id】
	'download_type' => isset($_GET['download_type']) ? $_GET['download_type'] : 0,  //卸载页面 download_type为1：立即下载 2：提交手机卸载
);
permanentlog('install_log_'.$h.'.json', json_encode($tolog));

header("Location:".$url);
exit;		
		
