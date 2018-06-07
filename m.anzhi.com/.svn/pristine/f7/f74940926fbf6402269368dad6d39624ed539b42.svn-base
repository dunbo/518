<?php
/*
* 百度广告跳转
* 
*/
include_once (dirname(realpath(__FILE__)).'/init.php');
$pkg = $_GET['package']; //广告对应包名
$is_game =  $_GET['game']; //是否是游戏，游戏跳转到对应详情自动下载，不进行极速下载
$chcode = $_GET['chcode'];
$keyword = urldecode($_GET['keyword']);
$encoding = mb_detect_encoding ( $keyword ,  "UTF-8,GBK" );
if ($encoding != 'UTF-8') {
	$keyword = mb_convert_encoding( $keyword, 'utf-8', $encoding);
}

if(empty($pkg)){
	header('Location: http://m.anzhi.com/baidu.html?chcode='.$chcode.'&keyword='.$keyword);
}else{
	$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $_GET['package']));
	if (empty($softinfo['ID'])){
		exit;
	}
	$softid = $softinfo['ID'];
	if ($is_game) {
		header('Location: http://m.anzhi.com/semgame_'.$softid.'.html?chcode='.$chcode.'&keyword='.$keyword);
	} else {
		header('Location: http://m.anzhi.com/sem_'.$softid.'.html?chcode='.$chcode.'&keyword='.$keyword);
	}
}