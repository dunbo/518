<?php
/*
** 页面加载此
*/
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$score		=	(int)$_GET['score'];

$tplObj->out['score']		=	$score;
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];

if( $is_weixin ) {
	$tplObj->display("lottery/{$prefix}/weixin.html");
}else {
	$tplObj->display("lottery/{$prefix}/result.html");
}
