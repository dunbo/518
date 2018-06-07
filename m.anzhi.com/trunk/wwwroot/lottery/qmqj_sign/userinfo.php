<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);
$url = $activity_host."/lottery/{$prefix}/index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}

$sign_num = get_sign_num($_SESSION['USER_UID']);

//获取我的礼包或礼券记录
$kind_award_list = get_user_kind_award_list($uid, $active_id, 'qmqj_sign', 'valentine_draw_award');

if( !empty($kind_award_list) ) {
	foreach ($kind_award_list as $key => $val ) {
		$award_info_key	=	"{$prefix}:{$active_id}_user_draw_award_info:{$uid}:{$val['level']}";
		$giftCode		=	$redis -> get($award_info_key);
		$kind_award_list[$key]['giftCode'] = isset($giftCode['giftCode']) ? $giftCode['giftCode'] : '';
	}
}

$tplObj -> out['kind_award_list']	=	$kind_award_list;
	
if($_GET['stop'] == 1){
	$tplObj -> out['stop'] = 1;
}

//是否是sdk
if($_SESSION['product']==1) {
	$is_sdk = 1;
}else {
	$is_sdk = 0;
}

//获取软件信息
$soft_info = getSoftInfoByAid($active_id);

$tplObj -> out['package'] = !empty($soft_info) ? $soft_info['package'] : '';//'sh.lilith.dgame.anzhi';//刀塔传奇包名
$tplObj -> out['is_sdk'] = $is_sdk;
$tplObj -> out['sign_num'] = $sign_num;
$tplObj -> out['username'] = $_SESSION['USER_NAME'];
$tplObj -> out['stop'] = isset($_GET['stop']) && $_GET['stop']==1 ? 1 : 0;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['aid'] = $active_id;	
$tplObj -> out['prefix'] = $prefix;	
$tplObj -> out['sid'] = $sid;
$tplObj -> out['is_test'] = $configs['is_test'];

$tpl = "lottery/{$prefix}/userinfo.html";

$tplObj -> display($tpl);
