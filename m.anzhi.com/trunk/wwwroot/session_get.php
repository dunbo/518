<?php
	$sid = $_GET['sid'];
	include(dirname(realpath(__FILE__)).'/functions.php');
	if(!$sid){
		echo "sid无效"; 
		exit;
	}
	$_COOKIE['PHPSESSID'] = '';
	//setcookie('PHPSESSID', '', time()-31536000);	
	session_begin($sid);
	user_loging_new();
	echo json_encode($_SESSION);
	exit;
