<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
header('content-type:text/html;charset=utf-8');
$chl_360 = '1eb231ac5c073ba76a2d0ba6df5d634c9750449c';
if(isset($_GET['s'])){
    $softid = (int)$_GET['s'];
}
$id = 0;
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
}

if($id){
    if (CHANNEL=='jinliqc') $channel_id = 1263;
    else $channel_id = 495;
	$model = load_model('packageqc');
    $package_qc = $model->getPacakgeInfoById($id,$channel_id);
	if(empty($package_qc)){
		exit;
	}
	$softinfo   = $soft_logic->get_soft_all_data_by_package($package_qc['package']);
	if(isset($softinfo['softid'])){
		$softid = $softinfo['softid'];
	}
}

if (empty($softid)) {
    exit;
}

//跳过下载认证的hostheader
$head_arr = array('360','wdj','soar','uu','mole','tencent','baidu');
$visit_host = explode('.',$_SERVER['HTTP_HOST']);
if(in_array($visit_host[0],$head_arr) || array_key_exists($channel,$chl_array)){//跳过下载认证
	$type = 1;
}else if(strstr($_SERVER['HTTP_HOST'],'anzhi.com')||strstr($_SERVER['HTTP_HOST'],'goapk.com')){
	$type = 2;
}else{
	exit;
}
if ((!$softn = (int)$_GET['n']) && ($type == 1) ) {
	$r = get_download_url($softid);
}else{
	$r = get_download_url($softid,"","0");
}
if ($r == DOWNLOAD_NO_SOFT) {
    if ($_GET['czy']) var_dump($r);
    exit;
}
$mark = $_GET['mark'];
list($downurl, $package, $ipbanned, $category) = $r;
if($softid == 211831 && $mark == 'r'){
$downurl = 'http://m.anzhi.com/redirect.php?do=dlapk&puid=589';
}
$h = date("H");
$dltime = time();


$ip = onlineip();
if ($ipbanned != DOWNLOAD_IPBANNED) {
	if ( $channel != "www" ) {
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

header("Location: {$downurl}");
