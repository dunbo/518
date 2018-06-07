<?php
	include_once (dirname(realpath(__FILE__)).'/init.php');
	$WALLPAPER_ID = $_GET['id'];
	if (!$WALLPAPER_ID){
		echo '参数错误！';
		return ;
	}
	$res = gomarket_action('konka.GoGetWapperDownload', array('WALLPAPER_ID' => $WALLPAPER_ID));
	header("Location: {$res['DATA']['INTEGRATE_URL']}");