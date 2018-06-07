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

if($_POST){
	$data = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'phone' => $_POST['mobile_phone'],
		'contact_name' => $_POST['lxname'],
		'address' => $_POST['address'],
	);
	$ret = add_user_new($data,$time,"{$prefix}","integral_userinfo");
    if($ret){
		//用户修改信息日志
		$log_data = array(
			'uid'=>$uid,
			'username' => $_SESSION['USER_NAME'],
			'phone' => $_POST['mobile_phone'],
			'contact_name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'time' => $time,
			'activity_id' => $active_id,
			'key' => 'info_edit'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'成功')));
	}else{
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else{
	if($_GET['types'] == 1){
		//抢夺记录
		$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","valentine_draw_gift");
		$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');
		$pid_arr = get_lottrey_pid();	
		foreach($kind_award_list as $k => $v){
			if($pid_arr[$v['pid']] >8){
				unset($kind_award_list[$k]);
			}
		}
		$kind_award_gift ? $kind_award_gift : $kind_award_gift = array();
		$kind_award_list ? $kind_award_list : $kind_award_list = array();
		$lottery_award =  array_merge($kind_award_gift,$kind_award_list);
		list($data,$p_total) = get_page($lottery_award,10);
		$tplObj -> out['kind_award_arr'] = $data;
		$tplObj -> out['p_total'] = $p_total;					
		$tplObj -> out['page'] = $_GET['page']?$_GET['page']:1;
		$pid = get_practicality_pid(1);
		$is_practicality = 0;
		foreach($data as $v){
			if($v['pid'] == $pid) $is_practicality = 1;
		}	
		$tplObj -> out['is_practicality'] = $is_practicality;		
	}else if($_GET['types'] == 2){
		//中奖信息
		$lottery_gift_key = "{$prefix}:{$active_id}_gift_prize:{$uid}:tmp";
		$lottery_gift = $redis -> get($lottery_gift_key);
		$lottery_award_key = "{$prefix}:{$active_id}_draw_award:{$uid}:tmp";
		$lottery_award =  $redis -> get($lottery_award_key);
		$lottery_gift ? $lottery_gift : $lottery_gift = array();
		$lottery_award ? $lottery_award : $lottery_award = array();		
		$lottery_award_tmp =  array_merge($lottery_gift,$lottery_award);
		//分割数组
		list($data,$p_total) = get_page($lottery_award_tmp,10);
		$tplObj -> out['lottery_award_tmp'] = $data;		
		$tplObj -> out['p_total'] = $p_total;	
		$tplObj -> out['pid_arr'] = get_lottrey_pid();			
		$tplObj -> out['page'] = $_GET['page']?$_GET['page']:1;	
		$pid = get_practicality_pid(1);
		$is_practicality = 0;
		foreach($data as $v){
			if($v['pid'] == $pid) $is_practicality = 1;
		}	
		$tplObj -> out['is_practicality'] = $is_practicality;				
	}else if($_GET['types'] == 4){
		$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');
		$pid_arr = get_lottrey_pid();	
		foreach($kind_award_list as $k => $v){
			if($pid_arr[$v['pid']] <=8){
				unset($kind_award_list[$k]);
			}
		}		
		list($data,$p_total) = get_page($kind_award_list,10);
		$tplObj -> out['p_total'] = $p_total;	
		$tplObj -> out['pid_arr'] = $pid_arr;			
		$tplObj -> out['page'] = $_GET['page']?$_GET['page']:1;			
		$tplObj -> out['kind_award_arr'] = $data;	
		$pid = get_practicality_pid(11);
		$is_practicality = 0;
		foreach($data as $v){
			if($v['pid'] == $pid) $is_practicality = 1;
		}	
		$tplObj -> out['is_practicality'] = $is_practicality;				
	}
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
	}
	
	$tplObj -> out['stop'] = isset($_GET['stop']) && $_GET['stop']==1 ? 1 : 0;
	$tplObj -> out['types'] = $_GET['types'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,"{$prefix}",'integral_userinfo');
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['sid'] = $sid;	
	
	if( isset($_GET['new']) ) {
		$tpl = "lottery/{$prefix}/userinfo_new.html";
	}else{
		$tpl = "lottery/{$prefix}/userinfo.html";
	}
	
	$tplObj -> display($tpl);	
}
