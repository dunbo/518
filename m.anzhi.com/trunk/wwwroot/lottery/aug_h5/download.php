<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$pkgname	=	$_POST['pkgname'];
$softid		=	$_POST['softid'];

$tplObj->display("lottery/{$prefix}/down.html");

