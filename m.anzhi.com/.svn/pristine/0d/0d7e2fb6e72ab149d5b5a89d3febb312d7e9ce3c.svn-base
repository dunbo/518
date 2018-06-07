<?php
include_once ('./fun.php');
session_begin($sid);
$build_query = http_build_query($_GET);
$url = $activity_host."/lottery/{$prefix}/index.php?".$build_query;

if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
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
	$kind_award_list = get_user_kind_award_new($uid,$active_id,$prefix,'pre_down_operation_award');
	$tplObj -> out['kind_award_arr'] = $kind_award_list;
	$kind_award_gift = get_user_kind_gift_new($uid,$active_id,$prefix,"pre_down_operation_gift");
	$tplObj -> out['gift_prize_arr'] = $kind_award_gift;	
	if(!$kind_award_gift && !$kind_award_list){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;//有中奖信息	
	}	
}else if($_GET['type'] == 2){
	//恭喜中奖
	$tplObj -> out['now'] = date('Y-m-d');
	$tplObj -> out['prizename'] = $_GET['prizename'];
	$tplObj -> out['package'] = $_GET['package'];
	$tplObj -> out['prize_type'] = $_GET['prize_type'];
	$gift_number = ($_GET['gift_number'] && $_GET['gift_number'] != 'null') ? $_GET['gift_number'] : '';
	$tplObj -> out['gift_number'] = $gift_number;
	$tplObj -> out['softname'] = $_GET['softname'];
}
$config = get_config($active_id,$_GET['ap_id']);
if($_GET['stop'] == 1){
	$option = array(
		'where' => array(
			'id' => $active_id,
		),
		'table' => 'sj_activity',
		'field' => 'name,activity_page_id,activity_end_id,end_tm',
		'cache_time' => 30*60
	);
	$activity = $model->findOne($option);		
	$start_config = get_config($active_id,$activity['activity_page_id']);
	$config['prize_back'] = $start_config['prize_back'];
	$config['prize_back_text_color'] = $start_config['prize_back_text_color'];
	$config['prize_bg_pic'] = $start_config['prize_bg_pic'];
	$config['prize_bg_color'] = $start_config['prize_bg_color'];
	$config['draw_button_color'] = $start_config['draw_button_color'];
	$config['prize_back_color'] = $start_config['prize_back_color'];
	$config['prize_text_color'] = $start_config['prize_text_color'];
	$config['no_prize_pic'] = $start_config['no_prize_pic'];
	$config['no_prize_text'] = $start_config['no_prize_text'];
}
$tplObj -> out['list'] = $config;
$tplObj -> out['img_url'] = getImageHost();		
$tplObj -> out['aid'] = $active_id;	
$tplObj -> out['sid'] = $sid;	
$tplObj -> out['prefix'] = $prefix;	
$tplObj -> out['stop'] = $_GET['stop'];	
$tplObj -> out['type'] = $_GET['type'];	
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
$tplObj -> display("lottery/{$prefix}/prize_list.html");	
