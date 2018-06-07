<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
$url = $activity_host."/lottery/{$prefix}/index.php?".$build_query;

if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$_SESSION['USER_ID'] = 13176;
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
if($_GET['type'] == 1){
	//我的奖品
	$kind_award_list = get_user_kind_award_new($uid,$active_id,$prefix,'sign_award');
	foreach($kind_award_list as $k => $v){
		$pos = strpos($v['prizename'],":");		
		$pos2 = strpos($v['prizename'],"礼券");
		if($pos2 !== false){
			$kind_award_list[$k]['type'] = 4;
		}else if($pos !== false){
			list($softname,$giftnum) = explode(":",$v['prizename']);
			$kind_award_list[$k]['prizename'] = $softname;
			$kind_award_list[$k]['gift_number'] = $giftnum;
			$kind_award_list[$k]['type'] = 5;
		}else{
			$kind_award_list[$k]['type'] = 1;
		}		
	}
	$tplObj -> out['kind_award_arr'] = $kind_award_list;		
}else if($_GET['type'] == 2){
	//恭喜中奖
	$tplObj -> out['now'] = date('Y-m-d');
	$tplObj -> out['prizename'] = $_GET['prizename'];
	$tplObj -> out['package'] = $_GET['package'];
	$gift_number = ($_GET['gift_number'] && $_GET['gift_number'] != 'null') ? $_GET['gift_number'] : '';
	$tplObj -> out['gift_number'] = $gift_number;
	$tplObj -> out['softname'] = $_GET['softname'];
}
$config = get_config($_GET['ap_id']);
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
	$start_config = get_config($activity['activity_page_id']);
	$config['bg_img'] = $start_config['bg_img'];
	$config['bg_color'] = $start_config['bg_color'];
	$config['button_pic'] = $start_config['button_pic'];
	$config['button_color'] = $start_config['button_color'];
	$config['award_color'] = $start_config['award_color'];
	$config['draw_button_color'] = $start_config['draw_button_color'];
	$config['prize_bg_color'] = $start_config['prize_bg_color'];
	$config['prize_text_color'] = $start_config['prize_text_color'];
	$config['prize_back_color'] = $start_config['prize_back_color'];
	$config['ranking_no_pic1'] = $start_config['ranking_no_pic1'];
	$config['warning_bgcolor'] = $start_config['warning_bgcolor'];
	$config['info_color'] = $start_config['info_color'];
	$config['is_filter'] = $start_config['is_filter'];
}
//用户信息
$userinfo = get_user_info_new($uid,$active_id,"{$prefix}","sign_userinfo");
if($userinfo['phone'] && $userinfo['contact_name'] && $userinfo['address']){
	$tplObj -> out['is_userinfo'] = 1;	
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
