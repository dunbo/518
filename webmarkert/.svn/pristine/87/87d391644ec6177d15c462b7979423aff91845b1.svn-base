<?php
//www,dev,bbs 都回调到 www 此脚本 清除__gosession
//session_start();
$config = array(
	'bbs' => array('http://bbs.anzhi.com/member.php?mod=logging&action=synlogout','http://bbs.anzhi.com'),
	'www' => array('http://www.anzhi.com/synlogout.php','http://www.anzhi.com'),
	'dev' => array('http://dev.anzhi.com/synlogout.php','http://dev.anzhi.com'),
	'rompk' => array('http://rompk.anzhi.com/synlogout.php','http://rompk.anzhi.com'), 
);
$refer = $_GET['refer'];
$js = '';
foreach($config as $site => $logout){
	if($site != $refer){
		$js .= '<script type="text/javascript" src="'.$logout[0].'"></script>';
	}
}
$cross_domain = preg_replace('/^([a-z0-9_-]+\.)*([a-z0-9_-]+\.[a-z0-9_-]+)$/', '\2', $_SERVER['HTTP_HOST']);
setcookie('__gosession', '', 0, '/', $cross_domain);

if($refer == 'bbs'){
	if(isset($_GET['ue']))	$url = urldecode($_GET['ue']);
	if($url) $config[$refer][1] = $url;
}
$host = $config[$refer][1];
$href .= $js.'<script>location.href="'.$host.'"</script>';
exit($href);