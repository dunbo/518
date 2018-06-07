<?php
require_once(dirname(realpath(__FILE__)) . '/init_page.php');
//opt 1.美女	2.猎奇
$opt	=	(int)$_POST['opt'];
$step	=	(int)$_POST['step'];
if( $opt == 1 ) {
	if( $step == 1 ) {
		//美女拼图
		$re	=	set_conf($opt, $step);
		if( $re ) {
			echo 1;die;
		}else {
			echo 0;die;
		}
	}elseif( $step == 3 ) {
		//美女视屏
		$re	=	set_conf($opt, $step);
		if( $re ) {
			echo 1;die;
		}else {
			echo 0;die;
		}
	}
}elseif( $opt == 2 ) {
	if( $step == 1 ) {
		//猎奇动图
		$re	=	set_conf($opt, $step);
		if( $re ) {
			echo 1;die;
		}else {
			echo 0;die;
		}
	}elseif( $step == 3 ) {
		//猎奇视屏
		$re	=	set_conf($opt, $step);
		if( $re ) {
			echo 1;die;
		}else {
			echo 0;die;
		}
	}
}
