<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!$_GET['ap_id'] && !ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url.$configs['activity_url']."/lottery/ranking/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
$time = time();
$today = date('Ymd');
$prefix = "ranking";
//获取配置
function get_config($aid,$ap_id){
	global $model;
	global $redis;
	global $prefix;
	if($ap_id){
		$activity_page_id = $ap_id;
	}else{	
		$option = array(
			'where' => array(
				'id' => $aid,
			),
			'table' => 'sj_activity',
			//'field' => 'activity_page_id,activity_end_id,end_tm',
			'cache_time' => 30*60
		);
		$activity = $model->findOne($option);	
		if($activity['end_tm'] <= time()){
			$activity_page_id = $activity['activity_end_id'];//结束pageid
		}else{
			$activity_page_id = $activity['activity_page_id'];
		}
	}
	$ranking_config_key = $prefix.":".$aid.":config:".$activity_page_id;
	$ranking_config = $redis->get($ranking_config_key);
	if($ranking_config === null){	
		$option = array(
			'where' => array(
				'ap_id' => $activity_page_id,
			),
			'table' => 'sj_activity_page',
		);
		$list = $model->findOne($option);	
		$list['is_score'] = intval($list['is_score']);
		$list['down_addlotterynum_switch'] = intval($list['down_addlotterynum_switch']);
		//处理连续抽奖数据
		if($list['ap_desc']){
			$list = array_merge($list,json_decode($list['ap_desc'],true));
			unset($list['ap_desc']);
		}
		$ranking_config = $list;
		$redis->set($ranking_config_key,$list,1200);		
	}
	return array($ranking_config,$activity);
}

function user_loging(){
	if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
		if (!empty($_COOKIE['_AZ_COOKIE_'])) {
			$ucenter = new GoUcenter('www');
			$cookie_data = $ucenter->parse_uc_cookie();
			unset($_SESSION['USER_ID']);
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
//跑马灯轮播前10条排行榜数据
function get_top10_ranking($config,$aid){
	$config_arr = explode('；',$config);
	global $model;
	$option = array(
		'table' => 'ranking_userinfo',
		'where' => array(
			'aid' => $aid,
			'money' => array('exp',' >0')
		),
		'field' => 'uid,money,username',
		'order' => 'money desc',
		'limit' => 10,
		'cache_time' => 10*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$list = array();	
	foreach($list_arr as $k=>$v){
		if(empty($v['username'])){
			$username = get_by_username($v['uid'],$aid);
			$username = str_replace_cn($username, 1, -2 );
		}else{
			$username = str_replace_cn($v['username'], 1, -2 );
		}			
		$search   = array("user","money");
		$replace  = array($username,$v['money']);
		$msgs = str_replace($search,$replace,$config_arr[$k]);			
		$list[$k]['msg'] = trim($msgs);
		$list[$k]['money'] = $v['money'];	
		$list[$k]['username'] = $username;	
	}
	//var_dump($list);exit;
	return $list;
}

//用户剩于抽奖次数、和充值总金额
function user_lottery_num($uid,$aid){
	global $model;
	global $redis;	
	//$redis->delete('ranking_user_money'.$uid.$aid);
	$money = $redis->get('ranking_user_money'.$uid.$aid);
	$lottery_num = $redis->get('ranking_rest_lottery_num'.$uid.$aid);
	if($money === null || $lottery_num === null){	
		$option = array(
			'table' => 'ranking_userinfo',
			'where' => array(
				'uid' => $uid,
				'aid' => $aid,
			),
			'field' => 'money, lottery_num,down_lottery_num,deduction_num'
		);
		$list = $model->findOne($option,'lottery/lottery');	
		//充值总金额	
		$money = intval($list['money']);
		$redis -> set("ranking_user_money".$uid.$aid,$money,30*86400);
		//剩于抽奖次数
		$lottery_num = intval($list['lottery_num']+$list['down_lottery_num']-$list['deduction_num']);
		if($lottery_num < 0){
			$lottery_num = 0;
		}
		$redis->set('ranking_rest_lottery_num'.$uid.$aid,$lottery_num,30*86400);
	}
	return array($money,$lottery_num);
}
//跑马灯轮播最近的10条中奖信息
function get_top10_prize($aid){
	global $model;
	global $redis;
	$option = array(
		'where' => array('status' =>1,'aid'=>$aid),
		'table' => 'gm_lottery_award',
		'field' => 'uid,pid',
		'order' => 'id desc',
		'limit' => 10,
		'cache_time' => 10*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	list($prize_arr,$prize_level) = get_prize_name($aid);
	$list = array();	
	foreach($list_arr as $k=>$v){
		$userinfo = get_user_info($v['uid'],$aid);
		if(empty($userinfo['username'])){
			$userinfo['username'] = get_by_username($userinfo['uid'],$aid);
			$redis->set('ranking_userinfo'.$v['uid'].$aid,$userinfo,10*86400);			
		}
		$list[$k]['username'] = str_replace_cn($userinfo['username'], 1, -2 );
		$list[$k]['prizename'] = $prize_arr[$v['pid']]['name'];
	}
	return $list;	
}
//获取奖品名
function get_prize_name($aid){
	global $model;	
	global $redis;	
	//$redis->delete('ranking_prize_name'.$aid);
	$prize_name = $redis->get('ranking_prize_name'.$aid);
	$new_prize_level = $redis->get('ranking_prize_level'.$aid);
	if($prize_name === null || $new_prize_level === null){
		$option = array(
			'where' => array('status' =>1,'aid'=>$aid),
			'table' => 'gm_lottery_prize',
			'field' => 'name,pid,level,pic_path,type',
			'order' => 'level asc',
		);
		$list_arr = $model->findAll($option,'lottery/lottery');	
		$prize_name = array();
		$prize_level = array();
		foreach($list_arr as $v){
			$prize_name[$v['pid']] = $v;
			$prize_level[$v['level']] = $v;
		}
		ksort($prize_level);	
		unset($list_arr);
		$i = 0;
		$new_prize_level = array();
		foreach($prize_level as $k => $v){
			$i++;
			$prize_name[$v['pid']]['i'] = $i;
			$new_prize_level[] = $v;
		}
		unset($prize_level);
		$redis->set('ranking_prize_name'.$aid,$prize_name,30*60);
		$redis->set('ranking_prize_level'.$aid,$new_prize_level,30*60);
	}
	return array($prize_name,$new_prize_level);
}
//用户信息获取
function get_user_info($uid,$aid){
	global $model;
	global $redis;		
	//$redis->delete('ranking_userinfo'.$uid.$aid);
	$user_list = $redis->get('ranking_userinfo'.$uid.$aid);
	if($user_list === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid
			),
			'table' => 'ranking_userinfo',
		);
		$user_list = $model->findOne($option,'lottery/lottery');	
		if($user_list){
			$redis->set('ranking_userinfo'.$uid.$aid,$user_list,60*86400);
		}
	}	
	return $user_list;			
}
//用户已用抽奖次数
function save_deduction_num($uid,$aid,$deduction_num){
	global $model;
	global $redis;		
	$time = time();
	$day = date('Ymd');
	$where = array(
		'uid' => $uid,
		'aid' => $aid,
	);
	$data_update = array(
		'deduction_num' => array('exp',"`deduction_num`+{$deduction_num}"),
		'update_tm' => $time,
		'__user_table' => 'ranking_userinfo'
	);
	return $model -> update($where,$data_update,'lottery/lottery');			
}
//安装获得一次抽奖机会
function add_down_lottery_num($uid,$aid){
	global $model;
	global $redis;		
	$time = time();
	$where = array(
		'uid' => $uid,
		'aid' => $aid,
	);
	$res = get_user_info($uid,$aid);
	if($res){
		$data_update = array(
			'down_lottery_num' => array('exp',"`down_lottery_num`+1"),
			'update_tm' => $time,
			'__user_table' => 'ranking_userinfo'
		);
		$ret = $model -> update($where,$data_update,'lottery/lottery');	
		if($ret){
			$redis->setx('incr','ranking_rest_lottery_num'.$uid.$aid,+1);	
		}		
	}else{
		$data = array(
			'uid' => $uid,
			'aid' => $aid,
			'username' => $_SESSION['USER_NAME'],
			'down_lottery_num' => 1,
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => 'ranking_userinfo'
        );
        $ret =  $model->insert($data,'lottery/lottery');
		if($ret){
			$redis->set('ranking_userinfo'.$uid.$aid,$data,10*86400);
			$redis->set('ranking_rest_lottery_num'.$uid.$aid,1,15*60);	
		}		
	}	
	return $ret;
}

//用户我的奖品--实物
function get_user_kind_award($uid,$aid){
	global $model;
	global $redis;		
	//$redis -> delete("ranking_draw_award{$uid}{$aid}");
	$kind_award_list = $redis -> getlist("ranking_draw_award{$uid}{$aid}");
    $is_hava_award = 0;
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid,
			),
			'table' => 'gm_lottery_award',
			'field' => 'id,uid,pid,prizename,gift_code',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		list($prize_name,$new_prize_level) = get_prize_name($aid);
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$v['prizename_old'] = $prize_name[$v['pid']]['name'];
			$v['type'] = $prize_name[$v['pid']]['type'];
			$kind_award_list[$v['id']] = $v;
			if($prize_name[$v['pid']]['type']==1){
				$is_hava_award = 1;
			}
		}
		unset($kind_award);
		$redis -> setlist("ranking_draw_award{$uid}{$aid}",$kind_award_list,1800);
	}else{
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}		
	}	
	return array($kind_award_list,$is_hava_award);
}
//用户我的奖品--礼包
function get_user_kind_gift($uid,$aid){
	global $model;
	global $redis;		
	//$redis -> delete("ranking_gift_prize{$uid}{$aid}");
	$gift_prize_list = $redis -> getlist("ranking_gift_prize{$uid}{$aid}");
	if(!$gift_prize_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => $uid,
				'aid' => $aid,
			),
			'table' => 'gm_virtual_prize',
			'field' => 'first_text,second_text,third_text,uid',
		);
		$kind_gift = $model->findAll($option,'lottery/lottery');	
		if(!$kind_gift) return false;
		$gift_prize_list = array();
		foreach((array)$kind_gift as $k => $v){
			$gift_prize_list[$v['first_text']] = $v;
		}
		$redis -> setlist("ranking_gift_prize{$uid}{$aid}",$gift_prize_list,1800);
		unset($kind_gift);
	}else{
		foreach($gift_prize_list as $k => $v){
			$gift_prize_list[$k] = json_decode($v,true);
		}		
	}
	return $gift_prize_list;
	
}
//排行榜数据
function get_page_ranking($p,$aid,$limit){
	global $model;
	$option = array(
		'table' => 'ranking_userinfo',
		'where' => array(
			'aid' => $aid
		),		
		'field' => 'money,username,uid',
		'order' => 'money desc',
		'limit' => $limit,
		'offset' => ($p - 1) * 10,
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	foreach($list_arr as $k => $v){
		if(empty($v['username'])){
			$username = get_by_username($v['uid'],$aid);
			$list_arr[$k]['username'] = str_replace_cn($username, 1, -2 );
		}else{
			$list_arr[$k]['username'] = str_replace_cn($v['username'], 1, -2 );
		}	
	}
	return $list_arr;
}
//所有用户实物中奖信息
function get_award_all($aid){
	global $model;
	global $redis;		
	//$redis -> delete("ranking_award_all{$aid}");
	$kind_award_list = $redis -> get("ranking_award_all{$aid}");
	$i = $redis -> get("ranking_award_all_count{$aid}");
	if($kind_award_list === null || $i === null){
		$option = array(
			'where' => array(
				'aid' => $aid,
				'status' => 1
			),
			'table' => 'gm_lottery_award',
			'field' => 'uid,pid',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		list($prize_name,$new_prize_level) = get_prize_name($aid);
		$kind_award_list = array();
		$i = 0;
		foreach((array)$kind_award as $k => $v){
			$i++;
			$userinfo = get_user_info($v['uid'],$aid);
			$kind_award_list[$k]['prizename'] = $prize_name[$v['pid']]['name'];
			if(empty($userinfo['username'])){
				$userinfo['username'] = get_by_username($v['uid'],$aid);
				$redis->set('ranking_userinfo'.$v['uid'].$aid,$userinfo,10*86400);
			}
			$kind_award_list[$k]['username'] = str_replace_cn($userinfo['username'], 1, -2 );		
		}
		unset($kind_award);
		$redis -> set("ranking_award_all{$aid}",$kind_award_list,60*60);
		$redis -> set("ranking_award_all_count{$aid}",$i,60*60);
	}
	return array($kind_award_list,$i);	
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
function get_by_username($uid,$aid){
	global $model;	
	$url = "http://open.anzhi.com/temp/getLoginName";
	//$url = "http://dev.i.anzhi.com:9011/temp/getLoginName";//测试环境
	$vals = array(
		'uid' => $uid
	); 
	$ret = httpGetInfo($url,'',$vals,'ranking_http_get_username.log');
	if($ret != 'not authorized' && $ret != 'not found uid' && $ret != 'uid not null'){
		$data = array(
			'username' => $ret,
			'__user_table' => 'ranking_userinfo'
		);
		$where = array(
				'uid' => $uid,
				'aid' => $aid,
		);
		$model->update($where, $data,'lottery/lottery');	
	}
	return $ret;
}
function is_utf8($word){   
     if(preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$word) == true) {   
		return true;   
     }else {   
		return false;   
     }   
}  
