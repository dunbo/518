<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
if($_POST['down']){
	//下载获取复活机会
	$res = get_down_flow();
	exit(json_encode($res));	
}else if($_POST['relive']){
	//领取复活机会
	$res = resurrection_number();
	exit(json_encode($res));		
}else if($_POST['use_relive_num']){
	//使用复活机会
	$res = use_resurrection_number();
	exit(json_encode($res));		
}
//当天场次
$screenings = get_screenings();

$tplObj -> out['imsi'] = $imsi;	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['prefix'] = $prefix;
list($down_num,$resurrection_num,$res_num) = get_res_num();
$tplObj -> out['down_num'] = $down_num;
$tplObj -> out['resurrection_num'] = $resurrection_num;
$tplObj -> out['res_num'] = $res_num;
$tplObj -> out['from'] = $_GET['from'];
$tpl = "lottery/".$prefix."/down.html";
$tplObj -> display($tpl);