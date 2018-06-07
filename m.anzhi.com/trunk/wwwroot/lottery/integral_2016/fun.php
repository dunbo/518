<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "integral_2016";
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
			'num' => intval($prize_list['num']),//数量
			'prizename' => $prize_list['name'],//奖品名称
			'start_time' => intval($prize_list['start_time']),//抢夺开始时间
			'refresh_time' => intval($prize_list['refresh_time']),//刷新频率
			'prize_integral' => intval($prize_list['prize_integral']),//总积分（总人次）
			'participants' => intval($kind_award['counts']),//已兑换数量
			'res_num' => intval(intval($prize_list['num'])-intval($kind_award['counts'])),//剩余奖品数量
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
	$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$res_integral_key = "{$prefix}:{$active_id}:res_integral:".$uid;
	$res_integral = $redis->get($res_integral_key);
	$lottery_num = $redis->get($lottery_num_key);
	if($res_integral === null || $lottery_num === null ){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'one_dollar_userinfo',
			'field' => 'integral_num,deduction_integral,lottery_num,deduction_num',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$res_integral = intval($rest_list['integral_num'])-intval($rest_list['deduction_integral']);
		$redis->set($res_integral_key,intval($res_integral),15*60);
		$lottery_num = intval($rest_list['lottery_num'])-intval($rest_list['deduction_num']);
		$redis->set($lottery_num_key,intval($lottery_num),15*60);	
	}	
	return array($res_integral,$lottery_num);			
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
	$kind_award_list = $redis -> getlist("{$prefix}:{$active_id}:kind_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'table' => 'one_dollar_prize',
			'where' => array(
				'aid' => $active_id,
			),
			'field' => 'id,position',
			'cache_time' => 30*60
		);		
		$prize_list = $model->findAll($option,'lottery/lottery');	
		$position = array();
		foreach($prize_list as $v){
			$position[$v['id']] = $v['position'];
		}
		unset($prize_list);
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'one_dollar_kind_award',
			'field' => 'id,uid,pid,prizename,status,integral',
			'order' => 'pid asc', 			
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			if($position[$v['pid']] == 4) continue;
			$kind_award_list[$v['id']] = $v;
			$kind_award_list[$v['id']]['position'] = intval($position[$v['pid']]);
		}
		unset($kind_award);
		$redis -> setlist("{$prefix}:{$active_id}:kind_award:{$uid}",$kind_award_list,15*60);
	}else{
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}	
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

function add_lottery_num($uid){
	global $redis;		
	global $model;
	global $active_id;		
	global $prefix;		
	$where = array(	
		'uid' => $uid,
		'aid' => $active_id,
	);
	$data = array(
		'lottery_num' => array('exp',"`lottery_num`+1"),
		'__user_table' => 'one_dollar_userinfo'
	);
	$model -> update($where,$data,'lottery/lottery');	
	//剩余抽奖次数
	$redis->setx('incr',"{$prefix}:".$active_id.":res_lottery_num:".$uid, +1);	
} 

