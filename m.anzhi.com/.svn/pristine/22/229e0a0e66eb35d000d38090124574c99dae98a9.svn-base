<?php
include_once ('./one_dollar_fun.php');
//$active_id =312;
$sid = $_GET['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin();
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/one_dollar/index.php?".$build_query;

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
	$time = time();	
	$option = array(
		'where' => array(
			'uid' => $uid,
		),
		'table' => 'one_dollar_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $data = array(
			'uid' => $userinfo['uid'],
			'username' => $userinfo['username'],
			'phone' => $_POST['mobile_phone'],
			'contact_name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'update_tm' => $time,
			'__user_table' => 'one_dollar_userinfo'
        );
        $where = array(
                'uid' => $uid
        );
        $ret =  $model->update($where, $data,'lottery/lottery');
    }else {//新增
        $data = array(
			'uid' => $uid,
			'username' => $_SESSION['USER_NAME'],
			'phone' => $_POST['mobile_phone'],
			'contact_name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => 'one_dollar_userinfo'
        );
        $ret =  $model->insert($data,'lottery/lottery');
    }
    $redis->set('one_dollar_user_info'.$uid,$data,86400*10);
	
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
			'key' => 'user_info'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'成功')));
	}else{
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else{
	if($_GET['types'] == 1){
		//抢夺记录
		$kind_award_list = get_user_kind_award($uid);
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
	}else if($_GET['types'] == 2){
		//兑换实物奖品
		$tplObj -> out['prizename'] = $_GET['prizename'];
		$tplObj -> out['integral'] = $_GET['num'];
	}
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
	}		
	$tplObj -> out['types'] = $_GET['types'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	//用户信息
	$userinfo= get_user_info($uid);
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> display('lottery/one_dollar/one_dollar_userinfo.html');	
}
