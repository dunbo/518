<?php
include_once ('./fun.php');
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['prefix'] = $prefix;	
if($active_id == 466 || $active_id == 480){
	$tplObj -> display("lottery/{$prefix}/rule.html");	
}else{
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['now'] = $time;
	$tplObj -> display("lottery/{$prefix}/ask_rule.html");	
}

