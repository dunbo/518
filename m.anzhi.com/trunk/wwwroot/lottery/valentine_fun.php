<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
function user_loging(){
	if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
		if (!empty($_COOKIE['_AZ_COOKIE_'])) {
			$ucenter = new GoUcenter('www');
			$cookie_data = $ucenter->parse_uc_cookie();
			if ($_SESSION['USER_ID'] != $cookie_data['pid']) {
				$user = $ucenter->token_userinfo();
				if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) {
					$_SESSION['USER_ID'] = $user['USER_ID'];
					$_SESSION['USER_UID'] = $user['USER_UID'];
					$_SESSION['USER_NAME'] = $user['USER_NAME'];
				}
			}
		}
	}	
	setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
	setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
}
//@剩余抽奖次数/用户金额/用户信息
function get_rest_valentine($uid){
	global $model;
	global $redis;		
	//$redis->delete('valentine_rest_num'.$uid);
	$rest = $redis->get('valentine_rest_num'.$uid);
	$user_info = $redis->get('valentine_user_info'.$uid);
	if($user_info === null || $rest === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'valentine_draw_userinfo',
		);
		$user_info = $model->findOne($option,'lottery/lottery');	
		//剩余抽奖次数
		$rest = $user_info['draw_data_num']-$user_info['deduction_num'];
		$redis->set('valentine_rest_num'.$uid,$rest,30*60);
		//用户金额
		$redis->set('valentine_user_info'.$uid,$user_info,30*60);
	}	
	return array($user_info,$rest);			
}

//跑马灯轮播最近的10条兑奖信息
function get_top10_prize(){
	global $model;
	$option = array(
		'where' => array('status' =>1),
		'table' => 'valentine_draw_award',
		'field' => 'username,prizename',
		'order' => 'id desc',
		'limit' => 10,
		'cache_time' => 5*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$list = array();	
	foreach($list_arr as $k=>$v){
		$list[$k]['username'] = str_replace_cn($v['username'], 1, -2 );
		$list[$k]['prizename'] = $v['prizename'];
	}
	return $list;	
}
//前10的排行榜用户
function get_top10_user(){
	global $model;
	$option = array(
		'table' => 'valentine_draw_userinfo',
		// 'where' => array(
			// 'money' => array('exp',">2000")
		// ),
		'field' => 'money,username',
		'order' => 'money desc',
		'limit' => 10,
		'cache_time' => 30*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$list = array();	
	foreach($list_arr as $k=>$v){
		$list[$k]['username'] = str_replace_cn($v['username'], 1, -2 );
		$list[$k]['money'] = $v['money'];
	}
	return $list;
}
//获取所有礼包-包名
function get_gift(){
	global $model;
	global $redis;	
	//$redis->delete('valentine_gift_pkg');	
	$prize_gift_pkg = $redis->get('valentine_gift_pkg');
	if(!$prize_gift_pkg){
		$option = array(
			'table' => 'valentine_draw_gift',
			'where' => array( 'status' => 0 ),
			'field' => 'package',
			'group' => 'package'
		);
		$list = $model->findAll($option,'lottery/lottery');	
		//echo $model->getSql()."\n";
		$prize_gift_pkg = array();		
		foreach($list as $k => $v){
			$prize_gift_pkg[] = $v['package'];		
		}
		$redis->set('valentine_gift_pkg',$prize_gift_pkg,86400*10);
	}	
	return $prize_gift_pkg;		
}

//用户礼包兑换信息
function get_user_kind_gift($uid){
	global $model;
	global $redis;		
	//$redis -> delete("valentine_gift_prize:{$uid}");
	$gift_prize_list = $redis -> getlist("valentine_gift_prize:{$uid}");
	$gift_prize_list = json_decode($gift_prize_list,true);
	if(!$gift_prize_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => $uid,
			),
			'table' => 'valentine_draw_gift',
			'field' => 'gift_number,uid,package,softname,update_tm',
		);
		$kind_gift = $model->findAll($option,'lottery/lottery');	
		if(!$kind_gift) return false;
		$gift_prize_list = array();
		foreach((array)$kind_gift as $k => $v){
			$gift_prize_list[$v['gift_number']] = $v;
		}
		$redis -> setlist("valentine_gift_prize:{$uid}",$gift_prize_list,86400*10);
		unset($kind_gift);
	}
	return $gift_prize_list;
	
}
//用户实物兑换信息
function get_user_kind_award($uid){
	global $model;
	global $redis;		
	//$redis -> delete("valentine_kind_award:{$uid}");
	$kind_award_list = $redis -> gethash("valentine_kind_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => $uid,
			),
			'table' => 'valentine_draw_award',
			'field' => 'id,uid,pid,prizename,create_tm',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$kind_award_list[$v['id']] = $v;
		}
		unset($kind_award);
		$redis -> setlist("valentine_kind_award:{$uid}",$kind_award_list,86400*10);
	}	
	return $kind_award_list;
}

//用户已用抽奖次数
function save_deduction_num($uid){
	global $model;
	$where = array('uid' => $uid);
	$data_update = array(
		'deduction_num' => array('exp',"`deduction_num`+1"),
		'update_tm' => time(),
		'__user_table' => 'valentine_draw_userinfo'
	);
	return $model -> update($where,$data_update,'lottery/lottery');			
}
//所有用户实物兑换信息
function get_user_award(){
	global $model;
	global $redis;		
	//$redis -> delete("valentine_kind_award:{$uid}");
	$award_list = $redis -> gethash("valentine_award");
	if(!$award_list){
		$option = array(
			'where' => array(
				'status' => 1,
			),
			'table' => 'valentine_draw_award',
			'field' => 'username,prizename',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$award_list = array();
		foreach((array)$kind_award as $k => $v){
			$award_list[$k]['username'] = str_replace_cn($v['username'], 1, -2 );
			$award_list[$k]['prizename'] = $v['prizename'];
		}
		unset($kind_award);
		$redis -> setlist("valentine_award",$award_list,86400*10);
	}	
	return $award_list;
}

function str_replace_cn($str, $start, $enlengthd ){  
 if(preg_match("/[\x7f-\xff]/", $str)){  
    if(is_utf8($str)){  
        return substr_replace($str,'***',$start*3, -1*3);  
    }else{  
        return substr_replace($str,'***',$start*2, -1*2);  
    }  
 }else{  
    return substr_replace($str,'***',$start*2, $enlengthd);  
 }  
}  
function is_utf8($word){   
     if(preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$word) == true) {   
      return true;   
     }else {   
      return false;   
     }   
}  