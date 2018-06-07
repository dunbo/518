<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
if($_GET['top_list']){
	//榜单页面
	list($top20,$top20_uid) = top_20();
	$tplObj -> out['top20'] = $top20;
	$tplObj -> out['top20_uid'] = $top20_uid;	
	$tpl = "lottery/".$prefix."/top_list.html";	
}else if($_GET['rule']){
	$tpl = "lottery/".$prefix."/rules.html";	
}else{
	//记录页
	list($prize,$prize2) = my_prize();
	$soft_info = get_soft_info($configs['is_test']);
	$tplObj -> out['soft_info'] = $soft_info;
	$tplObj -> out['my_prize'] = $prize;
	$tplObj -> out['my_prize2'] = $prize2;
	if(!$prize&&!$prize2){
		$tpl = "lottery/".$prefix."/my_prize_no.html";	
	}else{		
		$tpl = "lottery/".$prefix."/my_prize.html";	
	}
}
$tplObj -> out['imsi'] = $imsi;	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> display($tpl);