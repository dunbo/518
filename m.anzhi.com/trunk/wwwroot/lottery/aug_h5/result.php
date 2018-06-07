<?php
/*
** 页面加载此
*/
require_once(dirname(realpath(__FILE__)) . '/init_page.php');

$score		=	(int)$_GET['score'];
$is_share	=	$_GET['is_share'];

$tplObj->out['score']		=	$score;
$tplObj->out['is_share']	=	$is_share;
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
$tplObj->display("lottery/{$prefix}/result.html");