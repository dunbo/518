<?php
include_once ('./valentine_fun.php');
//$active_id =312;
$sid = $_GET['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin();

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	header("Location: http://promotion.anzhi.com/lottery/valentine.php");
}

if($_POST){
	$time = time();	
	$option = array(
		'where' => array(
			'uid' => $uid,
		),
		'table' => 'valentine_draw_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $data = array(
                'uid' => $userinfo['uid'],
                'username' => $userinfo['username'],
                'phone' => $_POST['mobile_phone'],
                'contact_name' => $_POST['lxname'],
                'address' => $_POST['address'],
                'money' => $userinfo['money'],
				'update_tm' => $time,
                '__user_table' => 'valentine_draw_userinfo'
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
				'money' => 0,
                'update_tm' => $time,
                'create_tm' => $time,
                'os_from' => 2,
                '__user_table' => 'valentine_draw_userinfo'
        );
        $ret =  $model->insert($data,'lottery/lottery');
    }
    $redis->set('valentine_user_info'.$uid,$data,86400*10);
	
    if($ret){
		//用户修改信息日志
		$log_data = array(
				'uid'=>$uid,
				"username" => $_SESSION['USER_NAME'],
                'phone' => $_POST['mobile_phone'],
                'contact_name' => $_POST['lxname'],
                'address' => $_POST['address'],
				'time' => $time,
				'key' => 'valentine_user_info'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'成功')));
	}else{
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else{
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
	}	
	if($_GET['types'] == 1){
		//已抽到的奖品、礼包
		$gift_prize_list = get_user_kind_gift($uid);
		$tplObj -> out['gift_prize_arr'] = $gift_prize_list;
		$kind_award_list = get_user_kind_award($uid);
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
	}else if($_GET['types'] == 2){
		//兑换实物奖品
		$tplObj -> out['prizename'] = $_GET['prizename'];
	}
	$tplObj -> out['types'] = $_GET['types'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	//用户信息
	list($userinfo,$rest) =get_rest_valentine($uid);
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> display('lottery/valentine_userinfo.html');	
}
