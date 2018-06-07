<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
//session_start();
if (!isset($_GET['softid']) && !isset($_GET['package']) && !isset($_GET['market'])) {
	exit;
}
if (isset($_GET['gcid'])) {
    $_SESSION['GAME_CID'] = intval($_GET['gcid']);
    checkGameChannel($_GET['gcid'], $_GET['package']);
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


if (isset($_GET['softid']) && isset($_GET['package'])) {
	exit;
}
else if (isset($_GET['softid']))
{
	$softid = $_GET['softid'];
}
else if (isset($_GET['package']))
{
	$model = new GoModel();
	$option = array(
		'table' => 'sj_soft_whitelist',
		'where' => array(
			'package' => $_GET['package'],
			'status' => 1,
		),
		'field' => 'id',
	);
	$result = $model->findOne($option);
    $anzhi_pkg = array('cn.goapk.market','anzhi.pad','com.azyx','com.anzhi.weixin','com.tencent.news');//添加腾讯新闻合作
    if ($_GET['package'] == 'com.tencent.news') {
        $_SESSION['MODEL_CID'] = 1577;
        $_SESSION['channel_soft_cid'] = 1577;
    }
	//if($result || in_array($_GET['package'],$anzhi_pkg)){	
		$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $_GET['package']));
	//}
	if (empty($softinfo['ID']))
	{
		exit;
	}
	$softid = $softinfo['ID'];
}
else if (isset($_GET['market']))
{
	$model = new GoModel();
	$option = array(
		'table' => 'sj_soft',
		'where' => array(
			'package' => 'cn.goapk.market',
			'status' => 1,
			'hide' => 1,
		),
		'order' => 'softid desc',
		'field' => 'softid',
	);
	$result = $model->findOne($option);
	if (!empty($result))
		$softid = $result['softid'];
}
else
{
	exit;
}

if (empty($softid))
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
	global $is_ip_banned,$configs;
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
	$channel = $_GET['channel'];
	$category = $app[$softid]['category_id'];
	//$category = strlen($category[1]) > 0 ? $category[1] : -1;
	if (!$is_ip_banned) { //如果不是ipbanned才记录日志
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
				'channel' => defined('CHL') ? CHL : $chl_code,
				'category' => $category,
				'refer' => $_SERVER['HTTP_REFERER']
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
			permanentlog('parter_'.$h.'.json', json_encode($tolog));
		}
		$tolog = array(
			'softid' => $softid,
			'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
			'action' => 10,//自己定的下载日志记录号
			'submit_tm' => $dltime,
			'package' => $package,
			'device' => $_SERVER['DEVICE'],
			'channel' => defined('CHL') ? CHL : $chl_code,
			'ip' => $ip,
			'category' => $category,
			'refer' => $_SERVER['HTTP_REFERER'],
			'download_type' => isset($_GET['download_type']) ? $_GET['download_type'] : 0,  //卸载页面 download_type为1：立即下载 2：提交手机卸载
			'gcid' => isset($_GET['gcid']) ? $_GET['gcid'] : 0,
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
        if ($_GET['from'] == 'zhiyoo') {
            $tolog['action'] = 20;//添加智友来源
        }
		//var_dump(json_encode($tolog));
		//exit();
		permanentlog('install_log_'.$h.'.json', json_encode($tolog));
		if (isset($_GET['market']) && $_GET['market_uninstall']) {
		    permanentlog('install_log_market_uninstall.json', json_encode($tolog));
		}
	}
	if($softid == 211831 && $_GET['k'] == 'r') {
		$downurl = 'http://m.anzhi.com/redirect.php?do=dlapk&puid=589';
		toLocation($downurl);
		exit;
	}
	$filename = "$package.apk";

    $s = array(
		'com.happyelements.AndroidAnimal' => 1,
		'cn.jj' => 1,
		'com.kiloo.subwaysurf' => 1,
		'com.imangi.templerun2' => 1,
		'com.moling.catchfish.ml' => 1,
		'com.og.danjiddz' => 1,
		'com.joym.xiongdakuaipao' => 1,
		'com.mykj.game.ddz' => 1,
		'com.example.mtracker' => 1,
		'com.brianbaek.popstar' => 1,
		'com.soulgame.bubble' => 1,
		'cn.goapk.market' => 1,
	);
    
	if ($package=='cn.goapk.market' && $_GET['from']!='old') {
		$url = 'http://m.anzhi.com/fast.php?chl_cid=935f9f6a466&type=ota';
		toLocation($url);
		exit;
	}

    if (isset($s[$package]) || stripos($package, 'anzhi') !== false) {
        $url = 'http://mm.apk.anzhi.com/'. base64_encode($app[$softid]['url']). '?type=base64';
        toLocation($url);
        exit;
    }

	if($_GET['type']=='channel'&&in_array($package,$configs['chl_package'])){
	    toLocation($configs['chl_package_url'][$package]);
	    exit;
	}

    $use_baidu = true;
    if ($use_baidu && !empty($app[$softid]['docurl']) && $app[$softid]['parentid']!=2) {
        ///百度迁移
        $url = $app[$softid]['docurl'];
    } else {
        $url = getApkHost($app[$softid]) . $app[$softid]['url'];
    }
if ($softid == 2631777) {
    $url = 'http://down.s.qq.com/download/apk/10017094_com.tencent.tmgp.sgame.apk';
}

    toLocation($url);
}



function get_short_soft_info($softid)
{
	static $infos = array();
	$model = new GoModel();
	if (!isset($infos[$softid])) {
		$option = array(
			'table' => 'sj_soft',
			'where' => array(
				'softid' => $softid
			),
			'field' => 'intro, update_content, softname, upload_tm, last_refresh'
		);
		$infos[$softid] = $model->findOne($option); 
	}
	return $infos[$softid];
}

function checkGameChannel($cid, $package)
{
	$redis = GoCache::getCacheAdapter('redis');
    $info = $redis->get("game_channel:softinfo:gcid:{$cid}:{$package}");

    if ($info) return true;

    $model = new GoModel();
    $last = time() - 3600;
	$option = array(
		'table' => 'sdk_channel_game',
		'where' => array(
			'channel_id' => $cid,
			'status' => 1,
			'apk_status' => 3,
			'package' => $package
		),
		'field' => 'package, softid, version_code_num , version_code, channel_id, url, filesize, md5_file, sign'
	);
	$v = $model->findOne($option);

	$part = "game_channel:softinfo:gcid:{$cid}:";
	$cache = array();
	if ($v) {
		$info = get_short_soft_info($v['softid']);
		$v['version'] = $v['version_code'];
		$v['version_code'] = $v['version_code_num'];
		$v['intro'] = $info['intro'];
		$v['update_content'] = $info['update_content'];
		$v['softname'] = $info['softname'];
		$v['upload_tm'] = $info['upload_tm'];
		$v['last_refresh'] = $info['last_refresh'];
		$key1 = $part. $v['softid'];
		$cache[$key1] = $v;
		$key2 = $part. $v['package'];
		$cache[$key2] = $v;
	}
	
	$option = array(
		'table' => 'sdk_channel_game_bak',
		'where' => array(
			'channel_id' => $cid,
			'status' => 1,
			'apk_status' => 3,
			'package' => $package,
		),
		'field' => 'package, softid, version_code_num, version_code, channel_id, url, filesize, md5_file, sign'
	);
	$v = $model->findOne($option);
	if ($v) {
		$info = get_short_soft_info($v['softid']);
		$v['version'] = $v['version_code'];
		$v['version_code'] = $v['version_code_num'];
		$v['intro'] = $info['intro'];
		$v['update_content'] = $info['update_content'];
		$v['softname'] = $info['softname'];
		$v['upload_tm'] = $info['upload_tm'];
		$v['last_refresh'] = $info['last_refresh'];
		$key1 = $part. $v['softid'];
		if (!isset($cache[$key1])) {
			$cache[$key1] = $v;
		}
		$key2 = $part. $v['package'];
		if (!isset($cache[$key2])) {
			$cache[$key2] = $v;
		}
	}
	if ($cache) {
		$cache['game_channel:cid:gcid:'. $cid] = 1;
		$redis->sets($cache, 7200);
	}
}