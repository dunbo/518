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
		<div class="banner_02">
			<img src="images/banner_02.jpg" alt=""/>
		</div>
		<div class="banner_03">
			<img src="images/banner_03.jpg" alt=""/>
		</div>
		<div class="active_intro">
			<div class="btns">
<?php
if (empty($sid))
{
?>
				<a href="javascript:void(0)" class="btns_01" onclick="window.open('taobaowebview://m.taobao.com/?weburl=http://m.taobao.com/channel/act/315/anzhi1111.xhtml?ttid=2236l005&actparam=1_38902_qd0005_anzhipush').location.href='notes_01.php';sleep(600)" ></a>
<?php
}
else
{
?>
				<a href="taobaowebview://m.taobao.com/?weburl=http://m.taobao.com/channel/act/315/anzhi1111.xhtml?ttid=2236l005&actparam=1_38902_qd0005_anzhipush" class="btns_01" onclick="sleep(600)" ></a>
<?php
}
?>
			</div>
		</div>
		<div class="active_txt">
			<h6>活动说明：</h6>
			<p>
				1、活动时间：2013年11月1日至11月10日<br/>
				2、参与本活动必须安装安智专享版淘宝客户端<br/>
				3、活动期间抢到的红包可在11月11日通过淘宝消费<br/>
				4、本次活动最终解释权归安智市场所有
			</p>
		</div>
	</div>
</div>
</body>
<script>
	function sleep(msec){
		//alert(msec);
		timer=setTimeout("jump('<?php if (empty($sid)) echo 'notes_01.php'; else echo "notes_01.php?sid=$sid";?>')", msec);
	}
	function jump(weburl){
		window.location.href=weburl;
	}
</script>
</html>
<?php 
	$ip = onlineip();
	$time = time();
	$data = array(
		'sid' => $sid,
		'ip'  => $ip,
		'time' => $time,
	);
	permanentlog('taobao_preheat.log', json_encode($data));
?>
