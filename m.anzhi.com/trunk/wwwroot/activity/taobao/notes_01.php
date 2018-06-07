<?php
	include_once('../../init.php');
	load_helper('utiltool');
	$sid = isset($_GET['sid']) ? $_GET['sid'] : '';
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta content="telephone=no" name="format-detection">
<meta name="keywords" content="Android,安卓,安卓网,安卓市场,安卓游戏,电子市场,安卓软件,Android游戏,安卓手机,游戏汉化,手机游戏,Android软件,软件下载,最新汉化软件,安卓游戏下载,游戏汉化" />
<meta name="description" content="安智市场,Android,安卓,安卓网,安卓市场,电子市场,安卓游戏,应用商店-国内最专业的Android安卓电子市场，提供海量安卓软件、Android手机游戏、安卓最新汉化软件、汉化游戏资源及最新APK汉化、汉化破解APP、APK免费下载，致力于为用户打造最贴心的Android安卓手机应用商店" />
<title>安智市场百万红包大回馈</title>
<link type="text/css" rel="stylesheet" href="css/common.css"/>
</head>
<body>
<div id="main">
	<div class="banner">
		<img src="images/banner_01.jpg" alt=""/>
	</div>
	<div class="content">
		<div class="pages_content">
			<p><strong>注意事项（如果您已成功参与，请忽略此页面）：</strong></p>
			<p>只有安装安智市场专享版淘宝客户端才能参与此活动。</p>
		</div>
	</div>
	<div class="footer">
	<div class="btns">
<?php
if (empty($sid))
{
?>
			<a href="#" class="btns_02" onclick="javascript:window.location.href='gomarket://details?id=com.taobao.taobao'"></a>
<?php
}
else
{
?>
			<!--<a href="javascript:void(0)" class="btns_03" onclick="javascript:window.AnzhiActivitys.downloadForActivity(1,1136276,'com.taobao.taobao','淘宝',88,21336319,7)"></a>-->
			<!--<a href="javascript:void(0)" class="btns_02" onclick="javascript:window.AnzhiActivitys.downloadForActivity(28,1157652,'com.taobao.taobao','淘宝',88,24310051,7)"></a>-->
			<a href="#" class="btns_02" onclick="javascript:window.location.href='gomarket://details?id=com.taobao.taobao'"></a>
<?php
}
?>
		</div>
	</div>
</div>
</body>
</html>
<?php 
	$ip = onlineip();
	$time = time();
	$data = array(
		'sid' => $sid,
		'ip'  => $ip,
		'time' => $time,
	);
	permanentlog('taobao_pre_download.log', json_encode($data));
?>