<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();

if($configs['is_test'] == 1 ) {
	$tplObj -> out['user_pic'] = "http://dev.i.anzhi.com/api/obtain/album?pid=".$_GET['azinvitepid'];
}else{
	$tplObj -> out['user_pic'] = "http://image.anzhi.com/header?pid=".$_GET['azinvitepid'];
}
$tplObj -> out['azinvitecode'] = $_GET['azinvitecode'];
$tplObj -> out['azinviteusername'] = $_GET['azinviteusername'];//邀请人
$tplObj -> out['versioncode'] = $_GET['versioncode'];
$tplObj -> out['uid'] = $_GET['azinvitepid'];
$tplObj -> out['rewardtype'] = $_GET['rewardtype'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_share_url'] = $configs['activity_share_url'];
if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
	$tplObj -> out['is_ios'] = 1;
}
$tpl = "lottery/red_ffl/share_inviter.html";
$tplObj -> out['is_weixin'] = is_weixin();

$send_arr = json_encode(array('KEY' =>'GET_APP_INFO','TASKMEMORY'=>1));
if($configs['is_test'] == 1 ) {
	$cil = "curl -d '{$send_arr}' 'http://api.test.anzhi.com/goserv.php?'";
}else{
	$cil = "curl -d '{$send_arr}' 'http://dev.gomarket.goapk.com/goserv.php?'";
}
$ret_num = shell_exec($cil);
$task_status = json_decode($ret_num,true);
		
// $simplesoft = load_model('simplesoft');
// $data = array('taskType'=>'T3');
// $task_status = $simplesoft->getTaskMemoryData($data,1);
$tplObj -> out['task_status'] = $task_status['data'][0] ? 1 : 0 ;
$tplObj -> display($tpl);