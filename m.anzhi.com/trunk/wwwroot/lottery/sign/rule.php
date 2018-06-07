<?php
include_once ('./fun.php');
$config = get_config($_GET['ap_id']);
$tplObj -> out['list'] = $config;
$img_host = getImageHost();
$tplObj -> out['img_url'] = $img_host;

$tplObj -> out['version_code']	=	$_SESSION['VERSION_CODE'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['prefix'] = $prefix;	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;	
$tplObj -> out['now'] = $time;
$tpl = "lottery/{$prefix}/rule.html";
$tplObj -> display($tpl);	


