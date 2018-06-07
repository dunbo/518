<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport"/>
<meta name="format-detection" content="telephone=no" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../css/Slideshow.css" />
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
  foreach($img as $k => $v)
  {
	if(!$v)
	{
	   unset( $img[$k] );
	   if(count($img)==1)
	   {
		$img1=getImageHost().$img[1];
	   }
	}
  }
?>
<form action="slide_new.php" method="post">
<div class="main" id="main">
	<div class="logo">
		<?php if($custom && $custom == "azyx") { ?>
		<img src="../images/about_logo2.png">
		<?php } else { ?>
		<img src="../images/about_logo.png">
		<?php } ?>
	</div>
	<h1 id="version" name="version">V<?php echo $version;?></h1>
	<?php if ($de_str) {?><h1><?php echo $de_str;?></h1><?php }?>
	<div class="infor">
		<?php if($custom && $custom=="azyx") { ?>
		<?php } else { ?>
		<div class="infor_1 arrow">
		<a target="_self" href="http://www.anzhi.com/wapcheck2.html?ot=1&title=安智官网">安智官网<span></span></a>
		<div class="line"></div>
		<a  id="new_info"  <?php if($result==null)
{?>href=""<?php } else if(count($img)==1) {?> href="one_pic.html?ot=1&title=新版介绍&cbm=1&sop=1&resolution= <?php echo $resolution ?>&verc=<?php echo $version_code; ?> <?php }  else { ?> href="new_intro.html?ot=1&title=新版介绍&cbm=1&sop=1&resolution=<?php echo $resolution?>&verc=<?php echo $version_code; ?> <?php  }?>" style="border-bottom:none;">新版介绍<span></span></a>
		</div>
		<?php } ?>
		<div class="infor_2 arrow">
		<?php 
		if($version_code>=5410 && $custom=='')
        {?>
		<a onClick="window.AnzhiActivitys.launchInviteInstall();" id="invite_install" ">邀请安装<span></span></a>
		<div class="line"></div>
	    <a target="_self" href="http://m.anzhi.com/help.html?ot=1&title=帮助说明">帮助说明<span></span></a>
		<div class="line"></div>
		<a target="_self" id="contact_us" href="contact_us.html?ot=1&title=联系我们&verc=<?php echo $version_code; ?>" style="border-bottom:none;">联系我们<span></span></a>
		<?php } else {?>
		  <a target="_self" href="http://m.anzhi.com/help.html?ot=1&title=帮助说明">帮助说明<span></span></a>
		  <div class="line"></div>
		<a target="_self" id="contact_us" href="contact_us.html?ot=1&title=联系我们&verc=<?php echo $version_code; ?>" style="border-bottom:none;">联系我们<span></span></a>
		<?php } ?>
		</div>
	</div>
</div>

<footer>
	<div id="footer" class="protocol" <?php if(($width=="480"&&$version_code>"5400")||$height<="320"||$width<="320") { ?> style=" position:relative;"<?php } else { ?> style=" position:fixed;" <?php } ?> >
    	<p style=""><a href="http://m.anzhi.com/xieyi.html?chcode=<?php echo $chcode; ?>&ot=1&title=安智用户协议">用户协议</a></p>
    </div>
</footer>
</form>
</body>
</html>
