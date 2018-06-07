<?php
include_once ('./fun.php');	
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['imsi'] = $imsi;	
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['now_tm'] = $time;	
if(is_weixin() || $_GET['is_weixin']){
	$tpl = "lottery/vip/weixin.html";	
	$tplObj -> display($tpl);	
	exit;
}
if($stop == 1){
	$tpl = "lottery/".$prefix."/end.html";	
	$tplObj -> display($tpl);	
	exit;
}

if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
if($_POST['send_code'] == 1){
	$mobile = $_POST['mobile_phone'];
	$ret = get_phone_ascription($mobile);	
	if($ret['ownoperator'] == '101'){
		exit(json_encode(array('code'=>0,'msg'=>'很抱歉，活动目前仅支持联通、移动、电信三大运营商流量包兑换~')));
	}
	$res = send_active_mobile($mobile);
	exit(json_encode($res));
}else if($_POST['binding'] == 1){
	$code = $_POST['code'];
	$mobile = $_POST['mobile_phone'];
	$res = check_mobile($mobile,$code);
	exit(json_encode($res));
}else if($_POST['use_log'] == 1){
	//礼券去使用日志
	$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $uid,
		'pkg' => $_POST['pkg'],
		'key' => 'go_use'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	exit(json_encode(array('code'=>1)));
} 

//日志
$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $uid,
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	

if($_SESSION['VERSION_CODE'] < 6000 || $imsi == ''){
	$soft_model = load_model('softlist');
	$anzhilist = $soft_model->getPackageToSoftId("cn.goapk.market");
	$anzhiid = array_pop($anzhilist);
	$soft_info = $soft_model ->getsoftinfos($anzhiid, getFilterOption());
	$tplObj -> out['alone_update'] = $alone_update;
	$tplObj -> out['soft_info'] = $soft_info[$anzhiid];
	$tpl = "lottery/".$prefix."/update.html";	
	$tplObj -> display($tpl);
	exit;
}
//当天场次
$screenings = get_screenings();
//var_dump($screenings,$time,$today);
$tplObj -> out['screenings'] = $screenings;	
$screenings_tm = get_screenings_tm();
$tplObj -> out['bind_status'] = get_bind_status();	
if($screenings == 1 || $screenings == 3){
	$is_answer_key = $prefix.":".$active_id.":is_answer:".$imsi.":".$screenings.":".$today;
	$exit_tab_key = $prefix.":".$active_id.":exit_tab:".$imsi.":".$screenings.":".$today;	
}else{
	$is_answer_key = $prefix.":".$active_id.":is_answer:".$uid.":".$screenings.":".$today;
	$exit_tab_key = $prefix.":".$active_id.":exit_tab:".$uid.":".$screenings.":".$today;
}
$is_answer = $redis->get($is_answer_key);
$exit_tab_key = $redis->get($exit_tab_key);
$tplObj -> out['is_answer'] = $is_answer;	
list($down_num,$resurrection_num,$res_num) = get_res_num();
$limit_list = get_limit_list();
if(isset($exit_tab_key) && !$res_num && $screenings>0){
	//有退出的记录并且没有去复活页面
	if($screenings == 5){
		$key =  date("Y-m-d",$time+86400);
		$tplObj -> out['countdown_tm'] = $screenings_tm[$key][1];	
		$tplObj -> out['limit_list'] = $limit_list[$today][1]['limit'];	
		$tplObj -> out['time'] = $limit_list[$today][1]['time'];
		$tplObj -> out['screenings'] = 1;			
	}else{
		//如果该场次已经参加过了直接倒计时下一场次
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][$screenings+1];
		$tplObj -> out['limit_list'] = $limit_list[$today][$screenings+1]['limit'];
		$tplObj -> out['time'] = $limit_list[$today][$screenings+1]['time'];
		$tplObj -> out['screenings'] = $screenings+1;					
	}
	$tplObj -> out['is_bind_user'] = bind_user();	
	
	$tpl = "lottery/".$prefix."/countdown.html";
}else if($screenings>0 && $time < $screenings_tm[$today][$screenings] || $is_answer){
	if($is_answer && $screenings == 5){
		$key =  date("Y-m-d",$time+86400);
		$tplObj -> out['countdown_tm'] = $screenings_tm[$key][1];	
		$tplObj -> out['limit_list'] = $limit_list[$today][1]['limit'];	
		$tplObj -> out['time'] = $limit_list[$today][1]['time'];
		$tplObj -> out['screenings'] = 1;	
	}else if($is_answer){
		//如果该场次已经参加过了直接倒计时下一场次
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][$screenings+1];
		$tplObj -> out['limit_list'] = $limit_list[$today][$screenings+1]['limit'];	
		$tplObj -> out['time'] = $limit_list[$today][$screenings+1]['time'];
		$tplObj -> out['screenings'] = $screenings+1;	
	}else{
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][$screenings];
		$tplObj -> out['limit_list'] = $limit_list[$today][$screenings]['limit'];
		$tplObj -> out['time'] = $limit_list[$today][$screenings]['time'];
		$tplObj -> out['screenings'] = $screenings;			
	}
	$tplObj -> out['is_bind_user'] = bind_user();	
	
	$tpl = "lottery/".$prefix."/countdown.html";
}else if($screenings == 1 || $screenings == 3){
	$tplObj -> out['limit_list'] = $limit_list[$today][$screenings]['limit'];	
	$tplObj -> out['time'] = $limit_list[$today][$screenings]['time'];	
	$tpl = "lottery/".$prefix."/index.html";	

}else if($screenings == 2 || $screenings == 4 || $screenings == 5 ){
	if($is_answer && $screenings == 5){
		$key =  date("Y-m-d",$time+86400);
		$tplObj -> out['countdown_tm'] = $screenings_tm[$key][1];	
	}else if($is_answer){
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][$screenings+1];
	}else{
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][$screenings];
	}
	$tplObj -> out['is_bind_user'] = bind_user();
	$tplObj -> out['limit_list'] = $limit_list[$today][$screenings]['limit'];	
	$tplObj -> out['time'] = $limit_list[$today][$screenings]['time'];		
	$tpl = "lottery/".$prefix."/index.html";
}else{
	//var_dump($screenings_tm,$screenings);
	if(!$screenings_tm[$today]){
		echo "不在活动期间";
		exit;
	}
	if($screenings == -1){
		//echo "当天已结束";
		$key =  date("Y-m-d",$time+86400);	
		$tplObj -> out['countdown_tm'] = $screenings_tm[$key][1];		
		$tplObj -> out['limit_list'] = $limit_list[$today][1]['limit'];	
		$tplObj -> out['time'] = $limit_list[$today][1]['time'];
		$tplObj -> out['screenings'] = 1;			
	}else if($screenings == -2){
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][2];	
		$tplObj -> out['limit_list'] = $limit_list[$today][2]['limit'];		
		$tplObj -> out['time'] = $limit_list[$today][2]['time'];
		$tplObj -> out['screenings'] = 2;			
	}else if($screenings == -3){
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][3];	
		$tplObj -> out['limit_list'] = $limit_list[$today][3]['limit'];	
		$tplObj -> out['time'] = $limit_list[$today][3]['time'];
		$tplObj -> out['screenings'] = 3;		
	}else{
		$tplObj -> out['countdown_tm'] = $screenings_tm[$today][1];	
		$tplObj -> out['limit_list'] = $limit_list[$today][1]['limit'];	
		$tplObj -> out['time'] = $limit_list[$today][1]['time'];	
		$tplObj -> out['screenings'] = 1;			
	}
	$tplObj -> out['is_bind_user'] = bind_user();	
	$tpl = "lottery/".$prefix."/countdown.html";	
}
$build_query = http_build_query($_GET);
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
$tplObj -> out['is_login'] = $uid ? 1 : 2;
$tplObj -> out['res_num'] = $res_num;
//$questions = random_questions(10,1);
//get_online_num($questions,1);
$tplObj -> display($tpl);