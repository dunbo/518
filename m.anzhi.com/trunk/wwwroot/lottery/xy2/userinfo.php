<?php
include_once ('./xy2_fun.php');
//$active_id =312;
$sid = $_GET['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin();
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/xy2/index.php?".$build_query;

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
	$now_day = date('Ymd');
	$option = array(
		'where' => array(
			'uid' => $uid,
		),
		'table' => 'xy2_draw_userinfo',
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
			'__user_table' => 'xy2_draw_userinfo'
        );
        $where = array(
                'uid' => $uid,
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
			'day' => $now_day,
			'os_from' => 2,
			'__user_table' => 'xy2_draw_userinfo'
        );
        $ret =  $model->insert($data,'lottery/lottery');
    }
    $redis->set('xy2_userinfo'.$uid,$data,86400*10);
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
		//我的奖品
		$kind_award_list = get_user_kind_award($uid);
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
		$kind_award_gift = get_user_kind_gift($uid);	
		$tplObj -> out['gift_prize_arr'] = $kind_award_gift;		
	}else if($_GET['types'] == 2){
		//恭喜中奖
		$tplObj -> out['prizename'] = $_GET['prizename'];
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
	$tplObj -> display('lottery/xy2/userinfo.html');	
}
