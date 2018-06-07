<?php
header('content-type:text/html;charset=utf-8');
if(isset($_GET['cid']) && $_GET['cid']>0){
	$cid=$_GET['cid'];
}else{
	$cid=0;
}
$url="m.anzhi.com";
$show = <<<EOT
(function(){
	document.writeln("<style>");
	document.writeln("*{margin:0;padding:0;}");
	document.writeln("body{font-family:Microsoft YaHei;font-size:14px;}");
	document.writeln("a,img{border:0}");
	document.writeln("a{text-decoration:none;color:#252525}");
	document.writeln(".anzhi_clear { clear: both; height: 0; line-height: 0; font-size: 0; overflow: hidden;}");
	document.writeln(".anzhi_ft_module{ width:100%; position:fixed; bottom:0;left:0px; z-index:999999; background: rgba(0,0,0,0.6);}");
	document.writeln(".anzhi_ft_btnA {color: #FFFFFF; padding:5px 2px 5px 4px;}");
	document.writeln(".anzhi_ft_btnA, .anzhi_ft_btnA dl {float:left; margin-top:3px;}");
	document.writeln(".anzhi_ft_btnA dl.anzhi_ft_margin15 {margin:0px 0 0 5px;}");
	document.writeln(".anzhi_ft_btnA dl p.anzhi_font16 {font-size:16px; margin-bottom:2px;}");
	document.writeln(".anzhi_ft_btnA dl p.anzhi_font12 {font-size:12px;}");
	document.writeln(".anzhi_ft_btnB { float:right; margin-right:5px; margin-top:8px;}");
	document.writeln(".anzhi_ft_btnB .anzhi_down_button { margin-top:5px; padding:5px; text-align:center; cursor:pointer; color:#FFFFFF; background-color:#7FC31E;}");
	document.writeln(".anzhi_ft_btnB .anzhi_down_button a {font-size: 14px;}");
	document.writeln(".anzhi_ft_close{float:right;height:100%;}");
	document.writeln(".anzhi_ft_close a{display:inline-block; background:urlhttp://img3.anzhi.com/img/201601/19/close_menu_logo.png) no-repeat 0 0px;width:20px; height:20px; cursor:pointer;}");
	document.writeln("<\/style>");
	document.writeln("<footer>");
	document.writeln("<div class=\"anzhi_ft_module\" id=\"anzhi_ft_module\">");
	document.writeln("<a href=\"http:\/\/{$url}\/redirect_new.php?cid={$cid}\">");
	document.writeln("<div class=\"anzhi_ft_btnA\">");
	document.writeln(" <dl><img src=\"http:\/\/{$url}/images/fudong_logo.png\" height=\"38\" width=\"38\" border=\"0\"><\/dl>");
	document.writeln("<dl class=\"anzhi_ft_margin15\">");
	document.writeln("<p class=\"anzhi_font16\">上安智&nbsp;&nbsp;下软件<\/p>");
	document.writeln("<p class=\"anzhi_font12\">不占内存&nbsp;&nbsp;省流量&nbsp;&nbsp;3亿用户都在用<\/p>");
	document.writeln("<\/dl>")
	document.writeln("<\/div>")
	document.writeln("<\/a>")
	document.writeln("<div class=\"anzhi_ft_close\">")
	document.writeln("<a href=\"javascript:;\" onclick=\"javascript:document.getElementById('anzhi_ft_module').style.display='none';\"><\/a>");
	document.writeln("<\/div>");
	document.writeln("<div class=\"anzhi_ft_btnB\">");
	document.writeln("<a href=\"http:\/\/{$url}\/redirect_new.php?cid={$cid}\"><dl class=\"anzhi_down_button\">立即下载<\/dl><\/a>");
	document.writeln("<\/div>");
	document.writeln("<div class=\"anzhi_clear\"><\/div>");
	document.writeln("<\/div>");
	document.writeln("<\/footer>");
}());
EOT;
echo $show;

