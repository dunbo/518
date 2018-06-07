<?php
include_once ('./fun.php');
if($_POST['is_address'] == 1){
	$ret = set_imsi_address();
	if($ret){
		//用户修改信息日志
		$log_data = array(
			"imsi" => $imsi,
			"device_id" => $_SESSION['DEVICEID'],	
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],			
			'sid' => $sid,	
			'award_id' => $_POST['id'],	
			'tel' => $_POST['mobile_phone'],
			'name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'time' => $time,
			'activity_id' => $active_id,
			'key' => 'info_edit'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'提交成功')));
	}else{
		exit(json_encode(array('code'=>0,'msg'=>'提交失败')));
	}
}
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
if($_GET['is_rule']){
	$tpl = "lottery/".$prefix."/rule.html";	
}else{
	$tpl = "lottery/".$prefix."/my_prize.html";	
	$tplObj -> out['list_prize'] = get_my_prize();
}
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['imsi'] = $imsi;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['lottery_prize']  =  get_lottery_prize();	
$tplObj -> out['prefix'] = $prefix;
$tplObj -> display($tpl);