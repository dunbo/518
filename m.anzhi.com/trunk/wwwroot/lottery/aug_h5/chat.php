<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$opt	=	(int)$_GET['opt'];
$rand	=	$_GET['rand'];

$tplObj->out['opt']		=	$opt;
$tplObj->out['rand']	=	$rand;
$tplObj->display("lottery/{$prefix}/chat.html");

