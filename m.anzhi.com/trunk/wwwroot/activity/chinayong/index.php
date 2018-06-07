<?php
	$sid = isset($_GET['sid'])&& !empty($_GET['sid'])?$_GET['sid']:'';
	$ip = $_SERVER['REMOTE_ADDR'];
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta content="telephone=no" name="format-detection">
<meta name="keywords" content="Android,安卓,安卓网,安卓市场,安卓游戏,电子市场,安卓软件,Android游戏,安卓手机,游戏汉化,手机游戏,Android软件,软件下载,最新汉化软件,安卓游戏下载,游戏汉化" />
<meta name="description" content="安智市场,Android,安卓,安卓网,安卓市场,电子市场,安卓游戏,应用商店-国内最专业的Android安卓电子市场，提供海量安卓软件、Android手机游戏、安卓最新汉化软件、汉化游戏资源及最新APK汉化、汉化破解APP、APK免费下载，致力于为用户打造最贴心的Android安卓手机应用商店" />
<title>天朝小将感恩大回馈</title>
<link type="text/css" rel="stylesheet" href="css/common.css?v3"/>
<script src="../../js/jquery.js"></script>
</head>
<style>
.border_color{border-color:#f00}
</style>
<body>
<div id="main">
	<div class="content">
		<img src="images/images_01.jpg" alt="">
	</div>
	<div class="content">
		<img src="images/images_02.jpg" alt="">
	</div>
	<div class="content" id="content3">
		<form method="post" action="">
			<input type="text" class="inputtext" id="username"/>
			<div class="clear"></div>
			<input type="text" class="inputtext" id="mobile_phone"/>
		</form>
		<span id="part_in"></span>
	</div>
	<div class="content">
		<img src="images/images_04.jpg" alt="">
	</div>
	<div class="content" id="content5">
		<img src="images/images_05.jpg" alt="">
	</div>
	<div id="content6">
		<a href="http://bbs.anzhi.com/thread-8200593-1-1.html"></a>
	</div>
</div>
<script	type="text/javascript">
	function isMobel(value)  {  
		if(/^13\d{9}$/g.test(value)||(/^15[0-35-9]\d{8}$/g.test(value))||(/^18[025-9]\d{8}$/g.test(value))){    
			return true;  
		}else{
			return false;  
		}  
	}
$(document).ready(function()
{
	$("#part_in").click(function(){
		var username = $("#username").val();
		var mobile_phone = $("#mobile_phone").val();
		var ip = '<?php echo $ip;?>';
		var sid = '<?php echo $sid;?>';
	if(username == '')
	 {	
		$('#username').removeClass('border_color');
		$('#mobile_phone').removeClass('border_color');
		$('#username').addClass('border_color');
		return false;
	 } 
	if(mobile_phone == '')
	 {  
	    $('#username').removeClass('border_color');
		$('#mobile_phone').removeClass('border_color');  	
		$('#mobile_phone').addClass('border_color');
		return false;
	 }
	 if(mobile_phone !='' && !isMobel(mobile_phone))
	 { 
		$('#username').removeClass('border_color');
		$('#mobile_phone').removeClass('border_color'); 
		$('#mobile_phone').addClass('border_color');
		return false;
	 }
	 if(username != '' && mobile_phone != '')
	 {  
		$('#username').removeClass('border_color');
        $('#mobile_phone').removeClass('border_color');
		$.getJSON("process.php",{ip:ip,sid:sid,username:username,mobile:mobile_phone},function()
		{
		  window.AnzhiActivitys.downloadForActivity(3,1005301,'com.xinmei365.games.xiaojiang','天朝小将(新版开启)',230,30749403,7);
		  
	  });
	 }
	});
});
</script>
</body>
</html>
