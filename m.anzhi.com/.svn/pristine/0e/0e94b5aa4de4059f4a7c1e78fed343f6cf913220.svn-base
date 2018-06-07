<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id =263;//278 518test
$sid = $_GET['sid'];

$build_query = http_build_query($_GET);
$center_url = "http://i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/integral.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;


//$active_id = $_GET['aid'];
session_begin();
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => time(),
		"award_level" => "",//pid
		"user" => $_SESSION['USER_NAME'],
		"name" => "",
		"telphone" => "",
		"address" => "",
		"package" => "",
		"gift" =>  "",
		"users" => "",
		'uid'=>$uid,
		"lottery_type" => "",//1实物2礼包
		"award_name" => "",
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
	if (!empty($_COOKIE['_AZ_COOKIE_'])) {
		$ucenter = new GoUcenter('www');
		$cookie_data = $ucenter->parse_uc_cookie();
		if (empty($_SESSION['user_data']) || $_SESSION['USER_ID'] != $cookie_data['pid']) {
			$user = $ucenter->token_userinfo();
			if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) {
				$_SESSION['USER_ID'] = $user['USER_ID'];
				$_SESSION['USER_UID'] = $user['USER_UID'];
				$_SESSION['USER_NAME'] = $user['USER_NAME'];
				$_SESSION['user_data'] = array(
					'login_account' => $cookie_data['loginAccount'],
					'user_name' => $user['USER_NAME'],
					'userid' => $user['USER_ID'],
				);
			}
		}
	} else {
		if (!empty($_SESSION['user_data'])) {
			unset($_SESSION['USER_ID']);
			unset($_SESSION['USER_UID']);
			unset($_SESSION['USER_NAME']);
			unset($_SESSION['user_data']);
		}
	}
}	
setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => time(),
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	//$uid = '20150507225440vnP2JWbVxE';
	$tplObj -> out['rest_integral'] = get_rest_integral($uid);
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
	//每日兑换次数
	$exchange_num = get_exchange_num($uid);
	$tplObj -> out['exchange_num'] = $exchange_num;		
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}
$tplObj -> out['aid'] = $active_id;
//剩余礼包数
$tplObj -> out['surplus_gift_num'] = get_surplus_gift();
//奖品
for ($i = 1; $i <= 8; $i++) {
	$prize = get_integral_prize($i);
	$tplObj -> out['prize_list_'.$i] = $prize;	
}
$tplObj -> out['num_arr'] = array(1,2,3,4,5,6,7,8);
//最近的10条兑奖信息
$top10_prize = get_top10_prize();
$tplObj -> out['top10_prize'] = $top10_prize;
$tplObj -> out['top10_prize_count'] = count($top10_prize);
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['now_time'] = $redis -> get("integral:now_time");
$tplObj -> out['next_time'] = $redis -> get("integral:next_time");
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('lottery/integral.html');

//每日兑换剩余次数
function get_exchange_num($uid){
	global $model;
	global $redis;
	//$redis->delete("integral_exchange_num{$uid}");
	$exchange_num = $redis->get('integral_exchange_num'.$uid);
	if($exchange_num === null){
		//计算当天领取的实物次数
		$option = array(
			'where' => array(
				'uid' => array('exp',"='{$uid}' and DATE_FORMAT(FROM_UNIXTIME(`time`),'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')"),
			),
			'table' => 'integral_kind_award',
			'field' => 'count(*) as counts',
		);
		$kind_award = $model->findOne($option,'lottery/lottery');	
		//计算当天领取的礼包次数
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => array('exp',"='{$uid}' and DATE_FORMAT(FROM_UNIXTIME(`update_tm`),'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')"),
			),
			'table' => 'integral_kind_gift',
			'field' => 'count(*) as counts',
		);
		$kind_gift = $model->findOne($option,'lottery/lottery');	
		$exchange_num = 5-$kind_award['counts']-$kind_gift['counts'];
		$r_tm = strtotime(date('Y-m-d').' 23:59:59')-time();
		$redis->set('integral_exchange_num'.$uid,$exchange_num,$r_tm);
	}	
	return $exchange_num;
}


//获取实物奖品列表
function get_integral_prize($id){
	global $model;
	global $redis;	
    //$redis->delete("integral_prize:{$id}");
	$prize_list = $redis->gethash("integral_prize:{$id}");
	if(!$prize_list){
		$option = array(
			'table' => 'integral_prize',
			'where' => array(
				'id' => array('exp',"<=8"),
				'rank' => $id,
			),
		);
		$prize_list = $model->findOne($option,'lottery/lottery');	
		$prize_list['num'] = intval($prize_list['num']);
		$prize_list['prize_integral'] = intval($prize_list['prize_integral']);
		$redis->sethash("integral_prize:{$id}",$prize_list,86400*10);
	}	
	return $prize_list;	
}
//@积分剩余量
function get_rest_integral($uid){
	global $model;
	global $redis;		
	//$redis->delete('rest_integral'.$uid);
	$rest = $redis->get('rest_integral'.$uid);
	if(!$rest){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'integral_userinfo',
			'field' => 'integral_num,deduction_integral',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$rest = $rest_list['integral_num']-$rest_list['deduction_integral'];
		$redis->set('rest_integral'.$uid,$rest,15*60);
	}	
	return $rest;			
}

//剩余礼包
function get_surplus_gift(){
	global $model;
	global $redis;
	//$redis->delete("integral_surplus_gift_num");
	$surplus_gift = $redis->get('integral_surplus_gift_num');
	if(!$surplus_gift){
		$option = array(
			'where' => array('status' => 0	),
			'table' => 'integral_kind_gift',
			'field' => 'count(*) as counts',
		);
		$surplus_gift = $model->findOne($option,'lottery/lottery');	
		$surplus_gift = intval($surplus_gift['counts']);
		$redis->set('integral_surplus_gift_num',$surplus_gift,86400*10);
	}	
	return $surplus_gift;	
} 
//跑马灯轮播最近的10条兑奖信息
function get_top10_prize(){
	global $model;
	$option = array(
		'where' => array('status' =>1),
		'table' => 'integral_kind_award',
		'field' => 'username,prizename',
		'order' => 'id desc',
		'limit' => 10,
		'cache_time' => 15*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$list = array();	
	foreach($list_arr as $k=>$v){
		$list[$k]['username'] = mb_substr($v['username'],0,2, 'UTF-8')."***".mb_substr($v['username'],5,3, 'UTF-8');
		$list[$k]['prizename'] = $v['prizename'];
	}
	return $list;
}