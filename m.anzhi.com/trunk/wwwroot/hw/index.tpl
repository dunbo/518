
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta content="telephone=no" name="format-detection">
<meta name="keywords" content="Android,安卓,安卓网,安卓市场,安卓游戏,电子市场,安卓软件,Android游戏,安卓手机,游戏汉化,手机游戏,Android软件,软件下载,最新汉化软件,安卓游戏下载,游戏汉化" />
<meta name="description" content="安智市场,Android,安卓,安卓网,安卓市场,电子市场,安卓游戏,应用商店-国内最专业的Android安卓电子市场，提供海量安卓软件、Android手机游戏、安卓最新汉化软件、汉化游戏资源及最新APK汉化、汉化破解APP、APK免费下载，致力于为用户打造最贴心的Android安卓手机应用商店" />
<title>安卓市场-Android,安卓,安卓网,安卓游戏,电子市场,国内最专业的Android安卓市场,提供海量安卓软件、安卓游戏、最新汉化软件、APK免费下载</title>
<link type="text/css" rel="stylesheet" href="/hw/css/style.css"/>
</head>
<body>
<section>
<div class="banner">
    <img src="/hw/images/banner.png"/>
</div>
<div class="content">
    <ul class="app-items-list" id="applistbox">
<!--{foreach from=$out.list item=v key=k}-->
        <li>
            <div class="itmes-icon"><img src="http://img3.anzhi.com<!--{$v.iconurl}-->" alt="<!--{$v.softname}-->" /></div>
            <div class="items-info">
                <h4><!--{$v.softname}--></h4>
                <div class="items-score">
                    <span class="items-score"><!--{$v.scorehtml}--></span>
                    <span class="items_size"><!--{$v.filesize}--></span>
                </div>
                <p><!--{$v.desc}--></p>
                <a class="down-btn" href="/hw/dl.php?package=<!--{$v.package}-->&vcode=hw">下载</a>
            </div>
        </li>
<!--{/foreach}-->
    </ul>
</div>
</section>
</body>
</html>
