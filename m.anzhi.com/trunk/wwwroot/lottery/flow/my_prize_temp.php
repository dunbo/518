<?php
include_once ('./fun_temp.php');
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$bind_list = get_bind_status();
$config_data = get_config($stop);
if($_POST['flow_extract'] == 1){
	$mobile = $bind_list['mobile'];
	if(!$mobile){
		exit(json_encode(array('code'=>0,'msg'=>'请先绑定手机号')));
	}
	$price = $_POST['price'];
	$ret = flow_recharge($mobile,$price);
	exit(json_encode($ret));
}
$tplObj -> out['img_url'] =  getImageHost();
$tplObj -> out['config_data'] = $config_data;
$grant_flow = grant_flow();
$my_prize = my_prize();
if($_GET['flow_up']){
	//提取页
	$tplObj -> out['res_flow'] = $my_prize['price_total']-$grant_flow['price_total'];
	$tplObj -> out['list_arr'] = $bind_list;
	$tplObj -> out['ascription'] = get_phone_ascription($bind_list['mobile']);
	// $tplObj -> out['ascription'] = get_phone_ascription(18310215648);
	// var_dump(getthemonth());
	$tplObj -> out['is_lastday'] = getthemonth();
	$tpl = "lottery/".$prefix."/flow_up_temp.html";		
}else if($_GET['flow_extract_succ'] == 1){
	$tplObj -> out['price'] =$_GET['price'];
	$tplObj -> out['activity_host'] = $configs['activity_url'];	
	$tpl = "lottery/".$prefix."/result_temp.html";	
}else if($_GET['is_rule'] == 1){
	$tpl = "lottery/".$prefix."/rules_temp.html";	
}else{
	//记录页
	$tpl = "lottery/".$prefix."/my_prize_temp.html";	
	$tplObj -> out['grant_flow'] = $grant_flow;
	$tplObj -> out['my_prize'] = my_prize();
	$tplObj -> out['mobile_str'] = substr($bind_list['mobile'],-4);
}
$tplObj -> out['imsi'] = $imsi;	
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> display($tpl);