<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
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


//用户剩于抽奖次数、和充值总金额
function user_lottery_num($uid){
	global $model;
	global $redis;	
	//$redis->delete('xy2_rest_lottery_num'.$uid);
	$money = $redis->get('xy2_user_money'.$uid);
	$lottery_num = $redis->get('xy2_rest_lottery_num'.$uid);
	if($money === null || $lottery_num === null){	
		$option = array(
			'table' => 'xy2_draw_userinfo',
			'where' => array(
				'uid' => $uid,
			),
			'field' => 'sum(money) as money,sum(draw_data_num) as draw_data_num,sum(deduction_num) as deduction_num'
		);
		$list = $model->findOne($option,'lottery/lottery');	
		//充值总金额	
		$money = intval($list['money']);
		$redis -> set("xy2_user_money".$uid,$money,10*60);
		//剩于抽奖次数
		$lottery_num = intval($list['draw_data_num'] - $list['deduction_num']);
		$redis->set('xy2_rest_lottery_num'.$uid,$lottery_num,10*60);
	}
	return array($money,$lottery_num);
}
//用户信息获取
function get_user_info($uid){
	global $model;
	global $redis;		
	//$redis->delete('xy2_userinfo'.$uid);
	$user_list = $redis->get('xy2_userinfo'.$uid);
	if($user_list === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'xy2_draw_userinfo',
		);
		$user_list = $model->findOne($option,'lottery/lottery');	
		$redis->set('xy2_userinfo'.$uid,$user_list,10*86400);
	}	
	return $user_list;			
}

//跑马灯轮播最近的10条中奖信息
function get_top10_prize(){
	global $model;
	$option = array(
		'where' => array('status' =>1),
		'table' => 'xy2_draw_award',
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

//用户我的奖品--实物
function get_user_kind_award($uid){
	global $model;
	global $redis;		
	//$redis -> delete("xy2_draw_award:{$uid}");
	$kind_award_list = $redis -> getlist("xy2_draw_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'xy2_draw_award',
			'field' => 'id,uid,pid,prizename,create_tm',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$kind_award_list[$v['id']] = $v;
			$kind_award_list[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
		}
		unset($kind_award);
		$redis -> setlist("xy2_draw_award:{$uid}",$kind_award_list,86400*10);
	}else{
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}		
	}	
	return $kind_award_list;
}
//用户我的奖品--礼包
function get_user_kind_gift($uid){
	global $model;
	global $redis;		
	//$redis -> delete("xy2_gift_prize:{$uid}");
	$gift_prize_list = $redis -> getlist("xy2_gift_prize:{$uid}");
	if(!$gift_prize_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => $uid,
			),
			'table' => 'xy2_draw_gift',
			'field' => 'gift_number,uid,package,softname,update_tm',
		);
		$kind_gift = $model->findAll($option,'lottery/lottery');	
		if(!$kind_gift) return false;
		$gift_prize_list = array();
		foreach((array)$kind_gift as $k => $v){
			$gift_prize_list[$v['gift_number']] = $v;
			$gift_prize_list[$v['gift_number']]['time'] = date("Y-m-d",$v['update_tm']);
		}
		$redis -> setlist("xy2_gift_prize:{$uid}",$gift_prize_list,86400*10);
		unset($kind_gift);
	}else{
		foreach($gift_prize_list as $k => $v){
			$gift_prize_list[$k] = json_decode($v,true);
		}		
	}
	return $gift_prize_list;
	
}
//所有实物中奖信息
function get_award_all(){
	global $model;
	global $redis;		
	$award_list = $redis -> getlist("xy2_draw_award");
	if(!$award_list){
		$option = array(
			'where' => array(
				'status' => 1,
			),
			'table' => 'xy2_draw_award',
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
		$redis -> setlist("xy2_draw_award",$award_list,86400*10);
	}else{
		foreach($award_list as $k => $v){
			$award_list[$k] = json_decode($v,true);
		}	
	}	
	return $award_list;
}
//用户已用抽奖次数
function save_deduction_num($uid,$user_name){
	global $model;
	global $redis;		
	$time = time();
	$day = date('Ymd');
	$where = array(
		'uid' => $uid,
		'day' => $day 
	);
	$res = $redis->setnx('xy2_userinfo_day'.$uid.$day,1,86400);	
	//$res = get_user($where);
	if($res === false){
		$data_update = array(
			'deduction_num' => array('exp',"`deduction_num`+1"),
			'update_tm' => $time,
			'username' => $user_name ? $user_name : $_SESSION['USER_NAME'],
			'__user_table' => 'xy2_draw_userinfo'
		);
		return $model -> update($where,$data_update,'lottery/lottery');			
	}else{
		$option = array(
			'uid' => $uid,
			'deduction_num' => 1,
			'money' => 0,
			'username' => $user_name ? $user_name : $_SESSION['USER_NAME'],
			'day' => $day,
			'update_tm' => $time,
			'create_tm' => $time,
			'__user_table' => 'xy2_draw_userinfo'
		);	
		return $model->insert($option,'lottery/lottery');
	}
}
function get_user($where){
	global $redis;	
	$user_day = $redis->get('xy2_userinfo_day'.$where['uid'].$where['day']);	
	if($user_day === null){
		global $model;	
		$option = array(
			'table' => 'xy2_draw_userinfo',
			'where' => $where,
			'field' => 'id'
		);
		$user_day = $model->findOne($option,'lottery/lottery');	
		if($user_day){
			$user_day = $redis->set('xy2_userinfo_day'.$where['uid'].$where['day'],1,86400);		
		}else{
			$redis -> delete("xy2_userinfo_day".$where['uid'].$where['day']);
		}
	}
	return $user_day;
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