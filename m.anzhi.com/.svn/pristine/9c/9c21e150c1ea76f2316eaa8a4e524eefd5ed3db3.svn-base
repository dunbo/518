<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "labor_day_2016";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
$time = time();

//获取实物奖品列表
function get_prize($position){
	global $model;
	global $redis;	
	global $active_id;	
	global $prefix;	
	$prize_info = $redis->gethash("{$prefix}:{$active_id}:prize:{$position}");
	if(!$prize_info){
		$option = array(
			'table' => 'one_dollar_prize',
			'where' => array(
				'position' => $position,
				'num' => array('exp'," >0"),
				'aid' => $active_id
			),
			'order' => 'rank asc', 
			'field' => 'id,prize_integral,name,start_time,refresh_time,pic_name',
		);
		$prize_list = $model->findOne($option,'lottery/lottery');	
		$option = array(
			'table' => 'one_dollar_kind_award',
			'where' => array(
				'pid' => $prize_list['id'],
				'start_time' => $prize_list['start_time'],
				'aid' => $active_id
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
			'res_participant' => intval(intval($prize_list['prize_integral'])-intval($kind_award['counts'])),//剩余人次
			'pic_name' => $prize_list['pic_name'],
		);
		$redis->sethash("{$prefix}:{$active_id}:prize:{$position}",$prize_info,86400*10);
	}	
	return $prize_info;	
}

//@积分剩余量
function get_rest_integral($uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$rest = $redis->get("{$prefix}:{$active_id}:res_integral:".$uid);
	if($rest === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'one_dollar_userinfo',
			'field' => 'integral_num,deduction_integral',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$rest = $rest_list['integral_num']-$rest_list['deduction_integral'];
		$redis->set("{$prefix}:{$active_id}:res_integral:".$uid,intval($rest),15*60);
	}	
	return $rest;			
}

//跑马灯轮播最近的10条中奖信息
function get_top10_prize(){
	global $model;
	global $active_id;
	$option = array(
		'where' => array('status' =>1,'aid'=>$active_id),
		'table' => 'one_dollar_kind_award',
		'field' => 'username,prizename',
		'order' => 'id desc',
		'limit' => 10,
		//'cache_time' => 5*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$list = array();	
	foreach($list_arr as $k=>$v){
		$list[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
		$list[$k]['prizename'] = $v['prizename'];
	}
	return $list;	
}

//用户抢夺记录
function get_user_kind_award($uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$kind_award_list = $redis -> gethash("{$prefix}:{$active_id}:kind_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
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
		$redis -> setlist("{$prefix}:{$active_id}:kind_award:{$uid}",$kind_award_list,15*60);
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
	global $active_id;		
	global $prefix;		
	//$redis -> delete("one_dollar_award");
	$award_list = $redis -> gethash("{$prefix}:{$active_id}:award");
	if(!$award_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'aid' => $active_id
			),
			'table' => 'one_dollar_kind_award',
			'field' => 'username,prizename',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$award_list = array();
		foreach((array)$kind_award as $k => $v){
			$award_list[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
			$award_list[$k]['prizename'] = $v['prizename'];
		}
		unset($kind_award);
		$redis -> setlist("{$prefix}:{$active_id}:award",$award_list,86400*10);
	}	
	return $award_list;
}
//计算奖品的中奖机率
function get_chances($uid){
	global $model;
	global $active_id;		
	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $active_id,
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

