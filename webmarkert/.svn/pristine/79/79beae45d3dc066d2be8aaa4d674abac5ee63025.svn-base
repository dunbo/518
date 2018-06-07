<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
header('content-type:text/html;charset=utf-8');
$chl_360 = '1eb231ac5c073ba76a2d0ba6df5d634c9750449c';
if(isset($_GET['s'])){
    $softid = (int)$_GET['s'];
    if ($softid == 643753) exit();
}
$id = 0;
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
}


if($id){
    $channel_id = $_SESSION['MODEL_CID'];
    $model = load_model('softlist');
    $package_qc = $model->getSoftInfo($id);
    if(empty($package_qc)){
        dl_exit();
    }
    $softinfo   = $model->getPkg2Id($package_qc[$id]['package']);
    if(!empty($softinfo)){
        $softid = array_pop($softinfo[$package_qc[$id]['package']]);
    }
}

if (empty($softid)) {
    dl_exit();
}
//北京地铁宣传
if($softid==1142658) {
$softid=1154573;
}
//跳过下载认证的hostheader
$head_arr = array('360','wdj','soar','uu','mole','tencent','baidu');
$visit_host = explode('.',$_SERVER['HTTP_HOST']);
if(in_array($visit_host[0],$head_arr) || array_key_exists($channel,$chl_array)){//跳过下载认证
    $type = 1;
}else if(strstr($_SERVER['HTTP_HOST'],'anzhi.com')||strstr($_SERVER['HTTP_HOST'],'goapk.com')){
    $type = 2;
}else{
    dl_exit();
}

$r = get_download_url($softid);

if ($r == DOWNLOAD_NO_SOFT) {
    dl_exit();
}
$mark = $_GET['mark'];
list($downurl, $package, $ipbanned, $category, $docurl, $softname) = $r;
if(empty($downurl)){
    dl_exit();
}
if($softid == 211831 && $mark == 'r'){
$downurl = 'http://m.anzhi.com/redirect.php?do=dlapk&puid=589';
}
$h = date("H");
$dltime = time();


$ip = onlineip();
if ($ipbanned != DOWNLOAD_IPBANNED) {
    if ( $channel != 'www') {
        $device_mark = isset($_GET['did']) ? $_GET['did'] : '';
        pu_load_model_obj('pu_log', array('logfile' => 'parter_'.$h.'.json', 'message' => json_encode(array(
                'softid' => $softid,
                'userid' => isset($_SESSION['user_data']['userid'])? $_SESSION['user_data']['userid'] : GO_UID_DEFAULT,
                'action' => $channel,
                'did' => $device_mark,
                'submit_tm' => $dltime,
                'package' => $package,
                //'chl' => strstr($channel, '360')? $chl_360 : '',
                'channel' => defined('CHL') ? CHL : $chl_code,
                'ip' => $ip,
                'category' => $category,
                'refer' => $_SERVER['HTTP_REFERER']
            ))
        ))->save_data_info();
    }
    pu_load_model_obj('pu_log', array('logfile' => 'install_log_'.$h.'.json', 'message' => json_encode(array(
                    'softid' => $softid,
                    'userid' => isset($_SESSION['user_data']['userid'])? $_SESSION['user_data']['userid'] : GO_UID_DEFAULT,
                    'action' => ACTION_WEB_DOWNLOAD,
                    'submit_tm' => $dltime,
                    'package' => $package,
                    'channel' => strstr($channel, '360')? $chl_360 : (defined('CHL') ? CHL : $chl_code),
                    'ip' => $ip,
                    'category' => $category,
                    'refer' => $_SERVER['HTTP_REFERER']
                    ))
    ))->save_data_info();
}

$pop_package = array(
    'com.tencent.mobileqq' => 1,
    'com.tencent.mm' => 1,
);

$limit_ua = array(
    'MEIZU' => 1,
    'AndroidDownloadManager' => 1,
);
$switch_market = false;
foreach ($limit_ua as $ua => $v) {
    $ua_quote = preg_quote($ua);
    if (preg_match('/'. $ua_quote. '/', $_SERVER['HTTP_USER_AGENT'])) {
        $switch_market = true;
        break;
    }
}
if ($_GET['channel'] == 'baidu') {
    if (stripos($_SERVER['HTTP_USER_AGENT'], 'wget') === false && stripos($_SERVER['HTTP_USER_AGENT'], 'curl') === false) {
        //非百度下载
        $_GET['n']=5;
        $switch_market = true;
    } else {
        $switch_market = false;
    }
}
$to_market = false;
if ($_GET['n']==5 && $switch_market) {
    $from_www = true;
    if (empty($_COOKIE['CKISP'])) {
        $from_www = false;
    } else {
        list($hash, $time) = explode('|', $_COOKIE['CKISP']);
        $hash1 = md5($_SERVER['HTTP_USER_AGENT'] . 'goapk'. $time. $_SERVER['REMOTE_ADDR']);
        if ($hash != $hash1) {
            $from_www = false;
        }
    }
//    if (!$from_www && mt_rand(1, 100) <= 60) {
    if (!$from_www) {
        $downurl = "http://m.anzhi.com/fast.php?type=details&softid={$softid}&chl_cid=39a9371f3326&log_action=100";
        $to_market = true;
    }
}
if ($docurl && !$to_market && $_GET['channel']!='yezi') {
    $downurl = $docurl;
}
if ($_GET['channel'] == 'sougou') {
    $downurl = str_replace('http://www.apk.anzhi.com/', 'http://aw.apk.anzhi.com/', $downurl);
    if ($softname) {
        $downurl = preg_replace('#http://[^\.]+\.apk.anzhi\.com/#', 'http://mw.apk.anzhi.com/', $downurl);
        $downurl .= '?f='. urlencode($softname). '.apk';
    }
    load_helper('ip2location'); 
    $locInfo = ipToLocation(onlineip());
    if ($locInfo['city'] != '北京') {
        $downurl = "http://m.anzhi.com/fast.php?type=details&softid={$softid}&chl_cid=166600574840&log_action=100";
    }
}


if ($_GET['channel']=='yezi' && $_GET['_track_d99957f7']) {
	$downurl .= '?_track_d99957f7='.$_GET['_track_d99957f7'] ;
}

if ($package=='cn.goapk.market') {
    $downurl = 'http://m.anzhi.com/fast.php?chl_cid=935f9f6a466&type=ota';
}


header("Location: {$downurl}");


function dl_exit()
{   
    global $tplObj;
    header("HTTP/1.0 404 Not Found");
    $tplObj -> display("404.html");
    exit;
}


