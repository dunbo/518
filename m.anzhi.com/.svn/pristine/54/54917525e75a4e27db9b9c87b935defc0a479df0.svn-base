<?php
include_once ('./fun.php');
if( $configs['is_test'] ) {
	$h_str = 'dev.';
}
$build_query = http_build_query($_GET);
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;

if(isset($_SESSION['USER_UID'])) {
	//已登录
	$tplObj -> out['is_login'] = 1;
}else {
	//未登录 
	$tplObj -> out['is_login'] = 2;
}

$tplObj -> out['login_url'] = $login_url;
$tplObj -> out['version_code']	=	$_SESSION['VERSION_CODE'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['prefix'] = $prefix;	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;	
$tplObj -> out['now'] = $time;
$award_all = get_award_all_new($active_id, $prefix, 'valentine_draw_award');
foreach($award_all as $k => $v){
	$pos = strpos($v['prizename'],":");
	if($pos !== false || !$v){
		unset($award_all[$k]);
	}
}
$tplObj -> out['award_all'] = $award_all;//获取获奖人信息奖品
$tpl = "lottery/{$prefix}/rule.html";
$tplObj -> display($tpl);	


