<?php
include_once ('./fun.php');
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/ranking/index.php?".$build_query;

if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}

if($_POST){
	$time = time();	
	$now_day = date('Ymd');
	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $active_id
		),
		'table' => 'ranking_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $data = array(
			'uid' => $userinfo['uid'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $_POST['mobile_phone'],
			'contact_name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'update_tm' => $time,
			'__user_table' => 'ranking_userinfo'
        );
        $where = array(
                'uid' => $uid,
				'aid' => $active_id,
        );
        $ret =  $model->update($where, $data,'lottery/lottery');
    }else {//新增
        $data = array(
			'uid' => $uid,
			'aid' => $active_id,
			'username' => $_SESSION['USER_NAME'],
			'phone' => $_POST['mobile_phone'],
			'contact_name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => 'ranking_userinfo'
        );
        $ret =  $model->insert($data,'lottery/lottery');
    }
    $redis->set('ranking_userinfo'.$uid.$active_id,$data,86400*10);
    if($ret){
		//用户修改信息日志
		$log_data = array(
			'uid'=>$uid,
			'sid'=>$sid,
			'username' => $_SESSION['USER_NAME'],
			'tel' => $_POST['mobile_phone'],
			'name' => $_POST['lxname'],
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
		//我的奖品
		list($kind_award_list,$is_hava_award) = get_user_kind_award($uid,$active_id);
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
		$tplObj -> out['is_hava_award'] = $is_hava_award;
		$kind_award_gift = get_user_kind_gift($uid,$active_id);	
		$tplObj -> out['gift_prize_arr'] = $kind_award_gift;		
	}else if($_GET['types'] == 2){
		//恭喜中奖
		$tplObj -> out['pptype'] = $_GET['pptype'];
		$tplObj -> out['prizename'] = $_GET['prizename'];
		$tplObj -> out['gift_num'] = $_GET['gift_num'];
	}
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
	}		
	$tplObj -> out['types'] = $_GET['types'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	//用户信息
	$userinfo= get_user_info($uid,$active_id);
	$version_code = $_SESSION['VERSION_CODE'];
	$tplObj -> out['version_code'] = $version_code;

	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['img_url'] = getImageHost();	
	list($ranking_config,$activity_arr)  = get_config($active_id);	
	$tplObj -> out['ranking_config'] = $ranking_config;		
	$tplObj -> display('lottery/ranking/userinfo.html');	
}
