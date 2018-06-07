<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
//session_start();
$ip = onlineip();
$threshold = 2000;//下载限制
	
if (isset($_GET['id']))
{
	$model = new GoModel();
	$hash_code = $_GET['id'];
	$option = array(
		'table' => 'sj_soft_download_map',
		'where' => array(
			'hash' => $hash_code,
			'status' => 1,
		),
		'field' => 'package',
	);
	$result = $model->findOne($option);
	if (empty($result))
		exit;
	else
		$package = $result['package'];

	$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $package));
	if (empty($softinfo['ID']))
	{
		exit;
	}
	$softid = $softinfo['ID'];
}
else
{
	exit;
}

//判断是否有个有效没有的话不让下
//load_helper('download');
//$can_download = is_can_donwload($softid);
//if (!$can_download) exit;

start_download($softid);

function start_download($softid) {
	global $softObj, $ip, $chl_code;
	global $hash_code;
	if (!empty($_SESSION['actid']))
	{
		$actid = $_SESSION['actid'];
	}
	if (!empty($_SESSION['phone']))
	{
		$phone = $_SESSION['phone'];
	}

	//$option = array('softid' => $softid,'status'=>1,'hide'=>array(0,1,1024));
	//$app = $softObj->getDataList('sj_soft', array('where' => $option));
	//$package = $app[0]['package'];
	//if (empty($package)) {
	//	header("HTTP/1.1 404 Not Found");
	//	exit;
	//}
	//$softfile = $softObj -> getDataList('sj_soft_file', array('where' => array('softid' => $softid,'package_status' => 1)));
	//if (!$softfile) {
	//	$softfiles = $softObj -> getDataList('sj_soft', array('where' => array('package' => $package,'hide' => 0))); //soft history
	//	uasort($softfiles, create_function('$a, $b', 'return $a["upload_tm"]<$b["upload_tm"]?1:-1;'));
	//	foreach($softfiles as $info){
	//		$softids[] = $info['softid'];
	//	}
	//	$softfile = $softObj -> getDataList('sj_soft_file',array('where' => array('softid' => $softids[0])));
	//}

	$model = load_model('softlist');
	$app = $model->getsoftinfos($softid, getFilterOption());
	//var_dump(getFilterOption());
	//exit();
    if (empty($app) || $app[$softid]['status'] == 0 || !in_array($app[$softid]['hide'], array(0, 1, 1024))) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	
	//包名不在上架列表中
	if (!$model->getPkg2Id($app[$softid]['package'])) {
		$log = array(
			'softid' => $softid,
			'package' => $app[$softid]['package'],
			'action' => 10,
			'ip' => onlineip(),
			'submit_tm' => time() ,
		);
		permanentlog('bad_download.json', json_encode($log));
		
		header("HTTP/1.1 404 Not Found");
		exit;			
	}
	
	$package = $app[$softid]['package'];


	$h = date("H");
	$dltime = time();
	$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
	$channel = 'aa50a800a02bcad9c7fcbed633392c777de6f5b0';
	$category = $app[$softid]['category_id'];
	//$category = strlen($category[1]) > 0 ? $category[1] : -1;
	if ( $channel ) {
		$device_mark = isset($_GET['did']) ? $_GET['did'] : '';
		$tolog = array(
			'softid' => $softid,
			'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
			'action' => $channel,
			'did'  => $device_mark,
			'submit_tm' => $dltime,
			'package' => $package,
			'ip' => $ip,
			'channel' => $channel,
			'category' => $category,
			'refer' => $_SERVER['HTTP_REFERER'],
			'ptn_code' => $hash_code,
		);
		if (!empty($actid))
		{
			$tolog['activity'] = array(
				'actid' => $actid,
			);
			if (!empty($phone))
			{
				$tolog['activity']['phone'] = $phone;
			}
		}
		permanentlog('download_ptn_'.$h.'.json', json_encode($tolog));
	}
	$tolog = array(
		'softid' => $softid,
		'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
		'action' => 10,//自己定的下载日志记录号
		'submit_tm' => $dltime,
		'package' => $package,
		'device' => $_SERVER['DEVICE'],
		'channel' => $channel,
		'ip' => $ip,
		'category' => $category,
		'refer' => $_SERVER['HTTP_REFERER'],
		'ptn_code' => $hash_code,
	);
	if (!empty($actid))
	{
		$tolog['activity'] = array(
			'actid' => $actid,
		);
		if (!empty($phone))
		{
			$tolog['activity']['phone'] = $phone;
		}
	}
	//var_dump(json_encode($tolog));
	//exit();
	permanentlog('install_log_'.$h.'.json', json_encode($tolog));
	$filename = "$package.apk";
	toLocation(getApkHost($app[$softid]) . $app[$softid]['url']);
}
