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

//获取实物奖品列表
function get_prize($position){
	global $model;
	global $redis;	
	//$redis->setx("HSET","one_dollar_prize:7","old_prizename",json_encode('移动充电宝21'));
    //$redis->delete("one_dollar_prize:{$position}");
	$prize_info = $redis->gethash("one_dollar_prize:{$position}");
	if(!$prize_info){
		$option = array(
			'table' => 'one_dollar_prize',
			'where' => array(
				'position' => $position,
				'num' => array('exp'," >0"),
			),
			'order' => 'rank asc', 
			'field' => 'id,prize_integral,name,start_time,refresh_time',
		);
		$prize_list = $model->findOne($option,'lottery/lottery');	
		$option = array(
			'table' => 'one_dollar_kind_award',
			'where' => array(
				'pid' => $prize_list['id'],
				'start_time' => $prize_list['start_time'],
			),
			'field' => 'sum(integral) as counts',
		);
		$kind_award = $model->findOne($option,'lottery/lottery');	
		$prize_info = array(
			'id' => $prize_list['id'],//奖品id
			'position' => $position,//位置
			'num' => 1,//数量
			'prizename' => $prize_list['name'],//奖品名称
			'start_time' => intval($prize_list['start_time']),//抢夺开始时间
			'refresh_time' => $prize_list['refresh_time'],//刷新频率
			'prize_integral' => intval($prize_list['prize_integral']),//总积分（总人次）
			'participants' => intval($kind_award['counts']),//已参与人次
			'res_participant' => (intval($prize_list['prize_integral'])-intval($kind_award['counts'])),//剩余人次
		);
		$redis->sethash("one_dollar_prize:{$position}",$prize_info,86400*10);
	}	
	return $prize_info;	
}

//@积分剩余量
function get_rest_integral($uid){
	global $model;
	global $redis;		
	$redis->delete('one_dollar_rest_integral'.$uid);
	$rest = $redis->get('one_dollar_rest_integral'.$uid);
	if($rest === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'one_dollar_userinfo',
			'field' => 'integral_num,deduction_integral',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$rest = $rest_list['integral_num']-$rest_list['deduction_integral'];
		$redis->set('one_dollar_rest_integral'.$uid,intval($rest),15*60);
	}	
	return $rest;			
}
//用户信息获取
function get_user_info($uid){
	global $model;
	global $redis;		
	//$redis->delete('one_dollar_user_info'.$uid);
	$user_list = $redis->get('one_dollar_user_info'.$uid);
	if($user_list === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'one_dollar_userinfo',
		);
		$user_list = $model->findOne($option,'lottery/lottery');	
		$redis->set('one_dollar_user_info'.$uid,$user_list,15*60);
	}	
	return $user_list;			
}

//跑马灯轮播最近的10条中奖信息
function get_top10_prize(){
	global $model;
	$option = array(
		'where' => array('status' =>1),
		'table' => 'one_dollar_kind_award',
		'field' => 'username,prizename',
		'order' => 'id desc',
		'limit' => 10,
		//'cache_time' => 5*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$list = array();	
	foreach($list_arr as $k=>$v){
		$list[$k]['username'] = str_replace_cn($v['username'], 1, -2 );
		$list[$k]['prizename'] = $v['prizename'];
	}
	return $list;	
}

//用户抢夺记录
function get_user_kind_award($uid){
	global $model;
	global $redis;		
	//$redis -> delete("one_dollar_kind_award:{$uid}");
	$kind_award_list = $redis -> gethash("one_dollar_kind_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'uid' => $uid,
			),
			'table' => 'one_dollar_kind_award',
			'field' => 'id,uid,pid,prizename,status,integral',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$kind_award_list[$v['id']] = $v;
		}
		unset($kind_award);
		$redis -> setlist("one_dollar_kind_award:{$uid}",$kind_award_list,15*60);
	}	
	return $kind_award_list;
}


function get_page_prize($p){
	$prize_info = array();
	if($p == 1 ){
		$arr = array(1,2,3,4);
	}else if($p == 2){
		$arr = array(5,6,7,8);
	}else if($p == 3){
		$arr = array(9,10,11,12);
	}
	foreach ($arr as $i) {
		$prize = get_prize($i);
		$prize_info[$i] = $prize;
	}	
	return 	$prize_info;
}
//所有用户实物中奖信息
function get_user_award(){
	global $model;
	global $redis;		
	//$redis -> delete("one_dollar_award");
	$award_list = $redis -> gethash("one_dollar_award");
	if(!$award_list){
		$option = array(
			'where' => array(
				'status' => 1,
			),
			'table' => 'one_dollar_kind_award',
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
		$redis -> setlist("one_dollar_award",$award_list,86400*10);
	}	
	return $award_list;
}
//计算奖品的中奖机率
function get_chances($uid){
	global $model;
	$option = array(
		'where' => array(
			'uid' => $uid,
			'pid' => intval($_POST['prize_id']),
			'start_time' => $_POST['start_time'],
		),
		'table' => 'one_dollar_kind_award',
		'field' => 'integral',
	);
	$kind_award = $model->findOne($option,'lottery/lottery');		
	if($_POST['num'] == '包尾'){
		$chances = round((($kind_award['integral']+$_POST['res_participant'])/$_POST['prize_integral'])*100,2);
	}else{
		$chances = round((($kind_award['integral']+$_POST['num'])/$_POST['prize_integral'])*100,2);
	}
	return array('chances'=>$chances >100 ? 100 : $chances);
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