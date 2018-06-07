<?php
include_once ('./fun.php');
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/{$prefix}/index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
if($_GET['type'] == 1){
	//我的奖品
	$kind_award_list = get_user_kind_gift_new($uid,$active_id,"{$prefix}",'valentine_draw_gift');
	$tplObj -> out['kind_award_arr'] = $kind_award_list;	
}else{
	$tplObj -> out['gift_number'] = $_GET['gift_number'];	
	$tplObj -> out['prizename'] = $_GET['prizename'];	
}
$tplObj -> out['stop'] = $_GET['stop'];	
$tplObj -> out['type'] = $_GET['type'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['aid'] = $active_id;	
$tplObj -> out['sid'] = $sid;	
$tplObj -> out['prefix'] = $prefix;	
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
$tplObj -> display("lottery/{$prefix}/userinfo.html");	

