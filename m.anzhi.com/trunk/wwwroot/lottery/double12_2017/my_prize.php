<?php
include_once ('./fun.php');

if($_SESSION['USER_UID']){
	//已登录
	$uid = $_SESSION['USER_UID'];
}else{
	//未登录 跳转到首页
	$url = $activity_host."/lottery/{$prefix}/index.php";
	header("Location: {$url}?aid={$aid}");
	exit;
}

//我的奖品
$kind_award_list = get_user_kind_award_list($uid, $active_id);
$tplObj -> out['kind_award_list'] = $kind_award_list;

$tplObj -> display("lottery/{$prefix}/my_prize.html");