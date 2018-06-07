<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

//获取实物奖品列表
function get_prize_list($position){
	global $model;
	global $redis;	
	//$redis->setx("HSET","christmas_prize:7","old_prizename",json_encode('移动充电宝21'));
    //$redis->delete("christmas_prize:{$position}");
	$prize_info = $redis->gethash("christmas_prize:{$position}");
	if(!$prize_info){
		$option = array(
			'table' => 'integral_prize',
			'where' => array(
				'rank' => $position,
				'num' => array('exp'," >0"),
				'is_luxury' => 3,
			),
			'order' => 'id asc', 
		);
		$prize_list = $model->findOne($option,'lottery/lottery');	
		$prize_info = array(
			'pid' => $prize_list['id'],//奖品id
			'position' => $position,//位置
			'num' => 1,//数量
			'prizename' => $prize_list['name'],//奖品名称
			'prize_integral' => intval($prize_list['prize_integral']),//奖品积分
			'pic_name' => $prize_list['pic_name'],//奖品图片名称
		);
		$redis->sethash("christmas_prize:{$position}",$prize_info,86400*10);
	}	
	return $prize_info;	
}
//刷奖品缓存
function brush_prize_cache($position,$old_prize_info){
	global $model;
	global $redis;	
	$option = array(
		'table' => 'integral_prize',
		'where' => array(
			'rank' => $position,
			'num' => array('exp'," >0"),
			'is_luxury' => 3,
		),
		'order' => 'id asc', 
	);
	$prize_list = $model->findOne($option,'lottery/lottery');
	if($prize_list){	
		$prize_info = array(
			'pid' => $prize_list['id'],//奖品id
			'num' => 1,//数量
			'sold_out' => 1,//售馨标识
			'position' => $position,//位置
			'prizename' => $prize_list['name'],//奖品名称
			'pic_name' => $prize_list['pic_name'],//图片名称
			'start_time' => intval(time()+$prize_list['re_time']),//抢夺开始时间
			'prize_integral' => intval($prize_list['prize_integral']),//总积分
			'old_prizename' => $old_prize_info['prizename'],
			'old_id' => $old_prize_info['pid'],
			'old_pic_name' => $old_prize_info['pic_name'],
			'old_prize_integral' => $old_prize_info['prize_integral'],
		);
		$redis->sethash("christmas_prize:{$position}",$prize_info,86400*10);	
		return $prize_info;
	}else{
		return false;
	}
}
//前10的抽奖奖品轮播数据
function get_top10_lottery($aid){
	global $model;
	global $redis;		
	//$redis -> delete("christmas_top10_lottery");	
	$top10_lottery = $redis -> getlist("christmas_top10_lottery");
	if(!$top10_lottery){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.aid' => $aid,
				'b.aid' => $aid,
				'b.is_luxury' => array(1,2),
			),
			'table' => 'integral_kind_award AS a',
			'field' => 'a.username,a.prizename',
			'order' => 'a.id asc',
			'limit' => 10,
			'join' => array(
				'integral_prize AS b' => array(
						'on' => array('a.pid', 'b.id')
				)
			)
		);		
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$top10_lottery = array();
		foreach((array)$kind_award as $k => $v){
			$top10_lottery[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
			$top10_lottery[$k]['prizename'] = $v['prizename'];
		}
		unset($kind_award);
		$redis -> setlist("christmas_top10_lottery",$top10_lottery,30*60);
	}else{
		foreach($top10_lottery as $k => $v){
			$top10_lottery[$k] = json_decode($v,true);
		}	
	}	
	return $top10_lottery;	
}
//前10的积分兑换轮播数据
function get_top10_integral_award($aid){
	global $model;
	global $redis;		
	//$redis -> delete("christmas_top10_integral_award");	
	$top10_lottery = $redis -> getlist("christmas_top10_integral_award");
	if(!$top10_lottery){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.aid' => $aid,
				'b.aid' => $aid,
				'b.is_luxury' => 3,
			),
			'table' => 'integral_kind_award AS a',
			'field' => 'a.username,a.prizename',
			'order' => 'a.id asc',
			'limit' => 10,
			'join' => array(
				'integral_prize AS b' => array(
						'on' => array('a.pid', 'b.id')
				)
			)
		);		
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$top10_lottery = array();
		foreach((array)$kind_award as $k => $v){
			$top10_lottery[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
			$top10_lottery[$k]['prizename'] = $v['prizename'];
		}
		unset($kind_award);
		$redis -> setlist("christmas_top10_integral_award",$top10_lottery,30*60);
	}else{
		foreach($top10_lottery as $k => $v){
			$top10_lottery[$k] = json_decode($v,true);
		}	
	}	
	return $top10_lottery;	
}
//用户中奖记录
function get_user_lottery_record($uid,$aid){
	global $model;
	global $redis;		
	//$redis -> delete("christmas_lottery_record:{$aid}:".$uid);	
	$lottery_record = $redis -> getlist("christmas_lottery_record:{$aid}:".$uid);
	if(!$lottery_record){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.uid' => $uid,
				'a.aid' => $aid,
				'b.aid' => $aid,
				'b.is_luxury' => array(1,2),
			),
			'table' => 'integral_kind_award AS a',
			'field' => 'a.*',
			'order' => 'a.id asc',
			'join' => array(
				'integral_prize AS b' => array(
						'on' => array('a.pid', 'b.id')
				)
			)
		);		
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$lottery_record = array();
		foreach((array)$kind_award as $k => $v){
			$lottery_record[$v['id']] = $v;
			$lottery_record[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
		}
		unset($kind_award);
		$redis -> setlist("christmas_lottery_record:{$aid}:".$uid,$lottery_record,30*60);
	}else{
		foreach($lottery_record as $k => $v){
			$lottery_record[$k] = json_decode($v,true);
		}	
	}	
	return $lottery_record;	
}
//用户积分兑换记录
function get_user_integral_kind_record($uid,$aid){
	global $model;
	global $redis;		
	//$redis -> delete("christmas_integral_kind_record:".$uid);	
	$lottery_record = $redis -> getlist("christmas_integral_kind_record:".$uid);
	if(!$lottery_record){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.uid' => $uid,
				'a.aid' => $aid,
				'b.aid' => $aid,
				'b.is_luxury' => 3,
			),
			'table' => 'integral_kind_award AS a',
			'field' => 'a.*',
			'order' => 'a.id asc',
			'join' => array(
				'integral_prize AS b' => array(
						'on' => array('a.pid', 'b.id')
				)
			)
		);		
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$lottery_record = array();
		foreach((array)$kind_award as $k => $v){
			$lottery_record[$v['id']] = $v;
			$lottery_record[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
		}
		unset($kind_award);
		$redis -> setlist("christmas_integral_kind_record:".$uid,$lottery_record,30*60);
	}else{
		foreach($lottery_record as $k => $v){
			$lottery_record[$k] = json_decode($v,true);
		}	
	}	
	return $lottery_record;	
}

//用户已用积分
function save_deduction_integral($uid,$aid,$user_name,$deduction_integral){
	global $model;	
	$time = time();
	$where = array(
		'uid' => $uid,
		'aid' => $aid 
	);
	$data_update = array(
		'deduction_integral' => array('exp',"`deduction_integral`+{$deduction_integral}"),
		'update_tm' => $time,
		'username' => $user_name ? $user_name : $_SESSION['USER_NAME'],
		'__user_table' => 'integral_userinfo'
	);
	return $model -> update($where,$data_update,'lottery/lottery');			

}
//用户剩余积分
function get_user_integral($uid,$aid){
	global $model;	
	global $redis;	
	$integral_num = $redis->get('christmas_rest_integral:'.$aid.":".$uid);
	if($integral_num === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid
			),
			'table' => 'integral_userinfo',
			'field' => 'integral_num,deduction_integral'
		);
		$userinfo = $model->findOne($option,'lottery/lottery');		
		$integral_num = intval($userinfo['integral_num']-$userinfo['deduction_integral']);		
		$redis->set('christmas_rest_integral:'.$aid.":".$uid,$integral_num,15*60);
	}
	return $integral_num;
}
//所有实物中奖信息和积分兑换信息
function get_award_all($aid){
	global $model;
	global $redis;		
	$award_list = $redis -> getlist("christmas_lottery_record_all");
	$integral_kind_record_all = $redis -> getlist("christmas_integral_kind_record_all");
	if(!$award_list || !$integral_kind_record_all){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.aid' => $aid,
				'b.aid' => $aid,
				'b.is_luxury' => array(1,2,3),
			),
			'table' => 'integral_kind_award AS a',
			'field' => 'a.username,a.prizename,b.is_luxury',
			'order' => 'a.id asc',
			'join' => array(
				'integral_prize AS b' => array(
						'on' => array('a.pid', 'b.id')
				)
			)
		);		
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$award_list = array();
		$integral_kind_record_all = array();
		foreach((array)$kind_award as $k => $v){
			if($v['is_luxury'] == 3){
				$integral_kind_record_all[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
				$integral_kind_record_all[$k]['prizename'] = $v['prizename'];	
			}else{
				$award_list[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
				$award_list[$k]['prizename'] = $v['prizename'];
			}
		}
		unset($kind_award);
		$redis -> setlist("christmas_integral_kind_record_all",$integral_kind_record_all,30*60);
		$redis -> setlist("christmas_lottery_record_all",$award_list,30*60);
	}else{
		foreach($award_list as $k => $v){
			$award_list[$k] = json_decode($v,true);
		}
		foreach($integral_kind_record_all as $k => $v){
			$integral_kind_record_all[$k] = json_decode($v,true);
		}	
	}	
	return array($award_list,$integral_kind_record_all);	
}
