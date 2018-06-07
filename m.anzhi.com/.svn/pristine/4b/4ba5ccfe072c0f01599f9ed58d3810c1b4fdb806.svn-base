<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');

$config = load_config('lottery_cache/redis',"lottery");

if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
//没有session 跳转到首页
session_begin();
$sid = $_GET['sid']?$_GET['sid']:$_POST['sid'];
$active_id =263;//278 518test
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	header("Location: http://promotion.anzhi.com/lottery/integral.php");
}

if($_POST){
	$option = array(
		'where' => array(
			'uid' => $uid,
		),
		'table' => 'integral_userinfo',
		'field' => 'phone,contact_name,address',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $data = array(
                'phone' => $_POST['mobile_phone'],
                'contact_name' => $_POST['lxname'],
                'address' => $_POST['address'],
				'update_tm' => time(),
                '__user_table' => 'integral_userinfo'
        );
        $where = array(
                'uid' => $uid
        );
        $ret =  $model->update($where, $data,'lottery/lottery');
    }else {//新增
        $data = array(
                'uid' => $uid,
                'phone' => $_POST['mobile_phone'],
                'contact_name' => $_POST['lxname'],
                'address' => $_POST['address'],
                'update_tm' => time(),
                'create_tm' => time(),
                'os_from' => 2,
                '__user_table' => 'integral_userinfo'
        );
        $ret =  $model->insert($data,'lottery/lottery');
    }
    $redis->set('integral_userinfo'.$uid,$data,86400*10);
	
    if($ret){
		exit(json_encode(array('code'=>1,'msg'=>'成功')));
	}else{
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else{
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
	}	
	if($_GET['types'] == 1){
		//已兑换的奖品
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
	$userinfo = get_userinfo($uid);
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];		
	$tplObj -> display('lottery/integral_userinfo.html');	
}

//用户礼包兑换信息
function get_user_kind_gift($uid){
	global $model;
	global $redis;		
	//$redis -> delete("integral_gift_prize:{$uid}");
	$gift_prize_list = $redis -> getlist("integral_gift_prize:{$uid}");
	$gift_prize_list = json_decode($gift_prize_list,true);
	if(!$gift_prize_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => $uid,
			),
			'table' => 'integral_kind_gift',
			'field' => 'gift_number,uid,package,softname,update_tm',
		);
		$kind_gift = $model->findAll($option,'lottery/lottery');	
		if(!$kind_gift) return false;
		$gift_prize_list = array();
		foreach((array)$kind_gift as $k => $v){
			$gift_prize_list[$v['gift_number']] = $v;
		}
		$redis -> setlist("integral_gift_prize:{$uid}",$gift_prize_list,86400*10);
		unset($kind_gift);
	}
	return $gift_prize_list;
	
}
//用户实物兑换信息
function get_user_kind_award($uid){
	global $model;
	global $redis;		
	//$redis -> delete("integral_kind_award:{$uid}");
	$kind_award_list = $redis -> gethash("integral_kind_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => $uid,
			),
			'table' => 'integral_kind_award',
			'field' => 'id,uid,pid,prizename,time',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$kind_award_list[$v['id']] = $v;
		}
		unset($kind_award);
		$redis -> setlist("integral_kind_award:{$uid}",$kind_award_list,86400*10);
	}	
	return $kind_award_list;
}

function get_userinfo($uid){
	global $model;
	global $redis;	
	$userinfo = $redis->get('integral_userinfo'.$uid);
	if(!$userinfo){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'integral_userinfo',
			'field' => 'phone,contact_name,address',
		);
		$userinfo = $model->findOne($option,'lottery/lottery');	
		$redis->set('integral_userinfo'.$uid,$userinfo,86400*10);
	}	
	return $userinfo;	
}