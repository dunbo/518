<?php
include_once ('./fun.php');
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/xy2/yd_index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}


	$tplObj -> out['static_url'] = $configs['static_url'];

	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
        $tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display('lottery/xy2/yd_set.html');
