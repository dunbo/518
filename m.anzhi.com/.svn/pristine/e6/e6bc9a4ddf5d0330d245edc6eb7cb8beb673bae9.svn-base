<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport"/>
<meta name="format-detection" content="telephone=no" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../css/Slideshow_v6.css?v3" />
<title>关于</title>
</head>

<body>
<?php
require_once(dirname(realpath(__FILE__)).'/../init.php');
$version=$_REQUEST['ver'];
$version=str_ireplace("V","",$version);
$version_code=$_REQUEST['verc'];
$resolution=$_REQUEST['resolution'];
$color_t=$_REQUEST['color_t'];
$chcode=trim($_REQUEST['chcode']);
$custom=trim($_REQUEST['custom']);
$de = array (
	'1eb4491947' => 1,
	'30c17b1e2561' => 1,
	'5a8a652f489' => 1,
	'd0f6f1ce2088' => 1,
	'2c9225283150' => 1,
	'19cb289f271' => 1,
	'2c9225283150' => 1,
);
$de_str = '';
if (isset($de[$chcode])) {
	$de_str = '安智内部测试版，数据无实际意义';
}

$width = substr($resolution,0,strrpos($resolution,'*'));
$height= substr($resolution,strrpos($resolution,'*')+1);
if (empty($version_code))
{
  exit;
}
$model = new GoModel();
$option = array(
		'where' => array(
			'verc' =>$_REQUEST['verc'] ,
		),
		'table' => 'sj_aboutslideshow'
	);
$result = $model -> findOne($option);
$img=array($result['img1'],$result['img2'],$result['img3'],$result['img4'],$result['img5']);
foreach($img as $k => $v) {
	if(!$v) {
		unset( $img[$k] );
		if(count($img)==1) {
			$img1=getImageHost().$img[1];
		}
	}
}
$icon = 'http://img3.anzhi.com/img/market/logo160_v645.png';
if($custom && $custom == "azyx") { 
	$icon = '../images/about_azyx2.png';
}
$version_desc = '';
$pid = 1;
if ($custom && $custom == "azyx") {
	if($version_code >= 6100) {
		$version_desc = "安卓游戏大厅V{$version}";
	} else {
		$version_desc = "安卓游戏V{$version}";
	}
} elseif ($custom == "smzdw") { 
	$pid = 20;
	$version_desc = "什么值得玩V{$version}";
	$icon = 'http://img3.anzhi.com/data3/img/201804/02/logo_smzdw.png';
} else {
	$version_desc = "安智市场V{$version}";
}

?>
<form action="slide_new.php" method="post">
<div class="main" id="main">
	<div class="logo">
		<img src="<?php echo $icon ?>">
	</div>
	<h1 id="version" name="version"><?php echo $version_desc;?></h1>
	<?php if ($de_str) {?><h1 style="margin:8px 0 0"><?php echo $de_str;?></h1><?php }?>
	<?php if($result['create_tm']){ ?>
	<div class="update_time">更新时间：<?php echo date("Y-m-d",$result['create_tm']);?></div>
	<?php } else { ?>
	<div class="update_time"></div>
	<?php } ?>
	<div class="infor">
		<?php if($custom == "") : ?>
		<a target="_self" href="http://www.anzhi.com/wapcheck2.html?ot=1&title=安智官网">安智官网<span></span></a>
		<?php if($result == "") : ?>
			<a  id="new_info" href="">新版介绍<span></span></a>
		<?php elseif(count($img)==1): ?>
			<a  id="new_info" href="one_pic.html?ot=1&title=新版介绍&cbm=1&sop=1&resolution= <?php echo $resolution ?>&verc=<?php echo $version_code; ?>">新版介绍<span></span></a>
		<?php else: ?>
			<a  id="new_info" href="new_intro.html?ot=1&title=新版介绍&cbm=1&sop=1&resolution=<?php echo $resolution?>&verc=<?php echo $version_code; ?>">新版介绍<span></span></a>
		<?php endif; ?>
		<?php endif; ?>

		<?php if($version_code>=5410 && $custom==''):?>
		<a onClick="window.AnzhiActivitys.launchInviteInstall();" id="invite_install" ">邀请安装<span></span></a>
	    <a target="_self" href="/help_new.html?ot=1&title=帮助说明">帮助说明<span></span></a>
		<a target="_self" id="contact_us" href="contact_us_v6.html?ot=1&title=联系我们&verc=<?php echo $version_code; ?>">联系我们<span></span></a>
		<?php elseif ($custom == "smzdw") : ?>
		  <a target="_self" href="/help.php?tpl_ver=v6&pid=20&ot=1&title=帮助说明">帮助说明<span></span></a>
	      <a target="_self" id="contact_us" href="contact_us_smzdw.html?ot=1&title=联系我们&verc=<?php echo $version_code; ?>">联系我们<span></span></a>			
		<?php else: ?>
		  <a target="_self" href="/help_new.html?ot=1&title=帮助说明">帮助说明<span></span></a>
	      <a target="_self" id="contact_us" href="contact_us_v6.html?ot=1&title=联系我们&verc=<?php echo $version_code; ?>">联系我们<span></span></a>
		<?php endif; ?>
	</div>
</div>

<footer>
	<div id="footer" class="protocol" <?php if(($width=="480"&&$version_code>"5400")||$height<="320"||$width<="320") { ?> style=" position:relative;"<?php } else { ?> style=" position:fixed;" <?php } ?> >
    	<p style=""><a href="/xieyi.html?chcode=<?php echo $chcode; ?>&ot=1&title=安智用户协议">用户协议</a></p>
    </div>
</footer>
</form>
</body>
</html>
