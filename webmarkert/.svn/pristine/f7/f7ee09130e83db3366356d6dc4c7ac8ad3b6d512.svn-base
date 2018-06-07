<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
header('content-type:text/html;charset=utf-8');
$chl_360 = '1eb231ac5c073ba76a2d0ba6df5d634c9750449c';

if(empty($_POST)){ exit; }
go_require_once(GO_APP_ROOT.'/checkcode/config.php');
if(formcheck($_POST['codedownid'])==false)
{
	echo "验证码错误,3秒后自动返回。如果没有自动返回上页，请点击返回。<a href=".$_SERVER[HTTP_REFERER]." >返回</a>";
	echo "<script type='text/javascript'>setTimeout('location.href="."\"".$_SERVER[HTTP_REFERER]."\""."',3000);</script>";
	exit;
}
if (!$softid = (int)$_GET['s']) {
    exit;
}
$r = get_download_url($softid,'1','1');
if ($r == DOWNLOAD_NO_SOFT) {
    exit;
}
list($downurl, $package, $ipbanned, $category) = $r;

if($softid == 211831 && $_GET['mark'] == 'r'){
$downurl = 'http://m.anzhi.com/redirect.php?do=dlapk&puid=589';
}
$h = date("H");
$dltime = time();

$channel = $_GET['channel'];
$ip = onlineip();
if ( $channel ) {
    $device_mark = isset($_GET['did']) ? $_GET['did'] : '';
    pu_load_model_obj('pu_log', array('logfile' => 'parter_'.$h.'.json', 'message' => json_encode(array(
            'softid' => $softid,
            'userid' => isset($_SESSION['user_data']['userid'])? $_SESSION['user_data']['userid'] : GO_UID_DEFAULT,
            'action' => $channel,
            'did' => $device_mark,
            'submit_tm' => $dltime,
            'package' => $package,
            //'chl' => strstr($channel, '360')? $chl_360 : '',
			'channel' => defined('CHL') ? CHL : '',
            'ip' => $ip,
            'category' => $category,
        ))
    ))->save_data_info();
}
pu_load_model_obj('pu_log', array('logfile' => 'install_log_'.$h.'.json', 'message' => json_encode(array(
                'softid' => $softid,
				'userid' => isset($_SESSION['user_data']['userid'])? $_SESSION['user_data']['userid'] : GO_UID_DEFAULT,
				'action' => ACTION_WEB_DOWNLOAD,
				'submit_tm' => $dltime,
				'package' => $package,
                'channel_id' => strstr($channel, '360')? $chl_360 : '',
                'ip' => $ip,
                'category' => $category,
				))
))->save_data_info(); 

header("Location: {$downurl}");

?>
