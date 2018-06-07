<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$opt	=	(int)$_GET['opt'];

 if($opt == 3) {
	$rand = rand(1,2);
 }
$tplObj->out['opt']		=	$opt;
$tplObj->out['rand']	=	$rand;
$tplObj->display("lottery/{$prefix}/select.html");

