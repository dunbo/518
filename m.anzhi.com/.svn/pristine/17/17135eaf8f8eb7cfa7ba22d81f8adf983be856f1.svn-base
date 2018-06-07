<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>安智市场手机版</title>
<script type="text/javascript">

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-7404902-2']);
_gaq.push(['_trackPageview']);

(function() {
 var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
 ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();
</script>

<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.STYLE1 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style></head>

<body>
<table width="100%" border="0" cellpadding="3" cellspacing="0">
  <tr>
    <td><img src="/img/logo.jpg" width="160" height="50" /></td>
  </tr>
  <tr>
    <td height="20" bgcolor="#CCCCCC"><span class="STYLE1">安智市场</span></td>
  </tr>
<?php
include dirname(__FILE__).'/../newgomarket.goapk.com/init.php';
$list = $softObj->getLatestMarkets();
foreach ($list as $idx => $market) {
    if ($market['firmware'] == '3') continue;
    $version_name = $market['version_name'];
    $url = $market['apkurl'];
    $size = $market['apksize'];
    $rom = $market['firmware'] == '3' ? 'Android 1.5' : 'Android 1.6/2.0/2.1/2.2/2.3';
    $size = (int)($size / 1024);
    $submit_tm = date("Y-m-d",$market['submit_tm']);
?>
  <tr>
    <td>软件版本：安智市场Gomarket v<?php echo $version_name; ?><br />
      固件版本：<?php echo $rom; ?><br />
      发布日期：<?php echo $submit_tm; ?><br />
      软件大小：<?php echo $size; ?> K<br />
      <img src="/img/down.png" width="16" height="16" />
      <a href="dl.php?apk=market">下载安智市场</a>
	  </td>
  </tr>
<?
}
?>
<tr>
<?
//$result = $softObj -> sum('pu_popularize', array('where' => array('apk' => substr($url,1)),'field' => 'dlcount'));
$marketapk = $softObj -> findAll(array('where' => array('cid' => 0,'status' => 1),'field' => 'apkurl','table' => 'sj_market'));
foreach($marketapk as $info){
  $apks[] = substr($info['apkurl'],1);
}
$result = $softObj -> findOne( array('where' => array('apk' => $apks),'field' => 'sum(dlcount) as sum','table' =>'pu_popularize'));
?>
 <td height="7">现已下载<?php echo 500000 + $result['sum'];?>次</td>
</tr>
  <tr>
    <td height="20" bgcolor="#CCCCCC"><span class="STYLE1">拍(pai)</span></td>
  </tr>
  <tr>
    <td>
      <img src="/img/down.png" width="16" height="16" />
	  <a href="/dl.php?apk=pai">下载快拍</a>
	  </td>
  </tr>
<tr>
<?
//$result = $softObj -> sum('pu_popularize', array('where' => array('apk' => 'pai.apk'),'field' => 'dlcount'));
$result = $softObj -> findOne(array('where' => array('apk' => 'pai.apk'),'field' => "sum(dlcount) as sum", 'table' => 'pu_popularize'))
?>
 <td height="7">现已下载<?php echo $result['sum'] ? $result['sum'] : 0;?>次</td>
</tr>
  <tr>
    <td height="20" bgcolor="#CCCCCC"><span class="STYLE1">快拍(kp)</span></td>
  </tr>
  <tr>
    <td>
      <img src="/img/down.png" width="16" height="16" />
	  <a href="/dl.php?apk=kp">快拍下载</a>
	  </td>
  </tr>
<tr>
<?
//$result = $softObj -> sum('pu_popularize', array('where' => array('apk' => 'kp.apk'),'field' => 'dlcount'));
$result = $softObj -> findOne(array('where' => array('apk' => 'kp.apk'),'field' => "sum(dlcount) as sum", 'table' => 'pu_popularize'))
?>
 <td height="7">现已下载<? echo $result['sum'] ? $result['sum'] : 0;?>次</td>
</tr>
  <tr>
    <td height="20" bgcolor="#CCCCCC"><span class="STYLE1">友情链接</span></td>
  </tr>
  <tr>
    <td><a href="http://www.goapk.com">安智网论坛</a>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="http://t.sina.com.cn/goapk">安智新浪微博</a>
        </td>
  </tr>
  <tr>
 <td>安智网. 版权所有|<script src="http://s16.cnzz.com/stat.php?id=3217453&web_id=3217453" language="JavaScript"></script></td>
  </tr>
</table>
</body>
</html>
