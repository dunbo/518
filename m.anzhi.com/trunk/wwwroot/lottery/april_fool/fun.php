<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "april_fool";
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
//$time = get_now_time();
/*
if(!isset($active_id)){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$active_id = 442;
	}else{
		$active_id = 410;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}
*/
//获取活动配置
function get_config($aid,$uid){
	global $model;
	global $redis;	
	global $prefix;	
	$tm_config = $redis->get("{$prefix}:{$aid}:tm_config:".$uid);	
	if($tm_config === null){	
		$option = array(
			'where' => array(
				'id' => $aid,
			),
			'table' => 'sj_activity',
			'field' => 'start_tm',
		);
		$activity = $model->findOne($option);
		$start =  $activity['start_tm'];
		$tm_config = array(
			date("Y-m-d",$start) => array(
				'num' => 1,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start)),
			),
			date("Y-m-d",$start+86400) => array(
				'num' => 2,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400)),
			),
			date("Y-m-d",$start+86400*2) => array(
				'num' => 3,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*2)),
			),
			date("Y-m-d",$start+86400*3) => array(
				'num' => 4,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*3)),
			),
			date("Y-m-d",$start+86400*4) => array(
				'num' => 5,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*4)),
			),
			date("Y-m-d",$start+86400*5) => array(
				'num' => 6,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*5)),
			),
			date("Y-m-d",$start+86400*6) =>array(
				'num' => 7,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*6)),
			),
		);
		$redis->set("{$prefix}:{$aid}:tm_config:".$uid,$tm_config,10*86400);
	}
	return $tm_config;			
}

function get_now_time(){
	global $model;	
	$option = array(
		'where' => array(
			'status' => 1,
			'conf_id' => 280
		),
		'table' => 'pu_config',
		'field' => 'configcontent',
	);
	$list = $model->findOne($option);	
	return strtotime($list['configcontent']);
}
//前30的抽奖奖品轮播数据
function get_top30_lottery($aid,$type){
	global $model;
	global $redis;		
	global $prefix;		
	if($type == 1){
		$top10_lottery = $redis -> getlist("{$prefix}:{$aid}:top10_lottery");
	}else{
		$top10_lottery = $redis -> getlist("{$prefix}:{$aid}:top10_down_lottery");
	}
	if(!$top10_lottery){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.aid' => $aid,
				'b.aid' => $aid,
				'a.type' => $type,
				'b.type' => $type,
				'b.level' => array('exp',"!=8"),
			),
			'table' => 'xy2_draw_award AS a',
			'field' => 'a.username,a.prizename',
			'order' => 'a.id desc',
			'limit' => 30,
			'join' => array(
				'xy2_draw_prize AS b' => array(
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
		if($type == 1){
			$redis -> setlist_sec("{$prefix}:{$aid}:top10_lottery",$top10_lottery,10*60);
		}else{
			$redis -> setlist_sec("{$prefix}:{$aid}:top10_down_lottery",$top10_lottery,10*60);
		}
	}else{
		foreach($top10_lottery as $k => $v){
			$top10_lottery[$k] = json_decode($v,true);
		}	
	}	
	return $top10_lottery;	
}

//用户转盘中奖记录、下载抽奖记录
function get_user_lottery_record($uid,$aid){
	global $model;
	global $redis;		
	global $prefix;		
	$recording = $redis -> getlist("{$prefix}:{$aid}:recording:".$uid);
	$lottery_record = $redis -> getlist("{$prefix}:{$aid}:lottery_record:".$uid);
	$down_lottery_record = $redis -> getlist("{$prefix}:{$aid}:down_lottery_record:".$uid);
	if(!$lottery_record || !$down_lottery_record || !$recording){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.uid' => $uid,
				'a.aid' => $aid,
				'b.aid' => $aid,
			),
			'table' => 'xy2_draw_award AS a',
			'field' => 'a.*,b.level',
			'order' => 'a.id asc',
			'join' => array(
				'xy2_draw_prize AS b' => array(
					'on' => array('a.pid', 'b.id')
				)
			)
		);		
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$lottery_record = array();
		$down_lottery_record = array();
		$recording = array();
		foreach((array)$kind_award as $k => $v){
			if($v['type'] == 1){
				if($v['level'] == 8){
					$recording[$v['id']] = $v;
					$recording[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
				}else{
					$lottery_record[$v['id']] = $v;
					$lottery_record[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
				}
			}else{
				$down_lottery_record[$v['id']] = $v;
				$down_lottery_record[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
			}
		}
		unset($kind_award);
		$redis->delete("{$prefix}:{$aid}:recording:".$uid);
		$redis->delete("{$prefix}:{$aid}:lottery_record:".$uid);
		$redis->delete("{$prefix}:{$aid}:down_lottery_record:".$uid);
		$redis -> setlist_sec("{$prefix}:{$aid}:recording:".$uid,$recording,86400);
		$redis -> setlist_sec("{$prefix}:{$aid}:lottery_record:".$uid,$lottery_record,86400);
		$redis -> setlist_sec("{$prefix}:{$aid}:down_lottery_record:".$uid,$down_lottery_record,86400);
	}else{
		foreach($recording as $k => $v){
			$recording[$k] = json_decode($v,true);
		}	
		foreach($lottery_record as $k => $v){
			$lottery_record[$k] = json_decode($v,true);
		}	
		foreach($down_lottery_record as $k => $v){
			$down_lottery_record[$k] = json_decode($v,true);
		}	
	}	
	return array($recording,$lottery_record,$down_lottery_record);	
}


//用户已用抽奖次数
function save_deduction($uid,$aid,$user_name,$is_luxury){
	global $model;	
	$time = time();
	$where = array(
		'uid' => $uid,
		'aid' => $aid 
	);
	$field = $is_luxury == 1 ? "turncard_num" : "turncard_num_sum";
	$data_update = array(
		$field => array('exp',"`{$field}`+1"),
		'update_tm' => $time,
		'username' => $user_name ? $user_name : $_SESSION['USER_NAME'],
		'__user_table' => 'xy2_draw_userinfo'
	);
	return $model -> update($where,$data_update,'lottery/lottery');			

}
//用户抽奖次数
function get_user_num($uid,$aid,$type){
	global $model;	
	global $redis;	
	global $prefix;		
	if($type == 1){
		$lottery_num = $redis->get("{$prefix}:".$aid.":rest_num:".$uid);	
	}else{
		$lottery_num = $redis->get("{$prefix}:{$aid}:rest_down_num:".$uid);
	}
	if($lottery_num === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid
			),
			'table' => 'xy2_draw_userinfo',
			'field' => 'gold_num,turncard_num,silver_num,turncard_num_sum'
		);
		$userinfo = $model->findOne($option,'lottery/lottery');		
		if($type == 1){
			//转盘
			$lottery_num = intval($userinfo['gold_num']-$userinfo['turncard_num']);
			$lottery_num = $lottery_num > 0 ? $lottery_num : 0;
			$redis->set("{$prefix}:".$aid.":rest_num:".$uid,$lottery_num,15*60);
		}else{
			//下载
			$silver_num = $userinfo['silver_num'] > 21 ? 21 : $userinfo['silver_num'];
			$lottery_num = intval($silver_num-$userinfo['turncard_num_sum']);
			$lottery_num = $lottery_num > 0 ? $lottery_num : 0;
			$redis->set("{$prefix}:{$aid}:rest_down_num:".$uid,$lottery_num,15*60);
		}
	}
	return $lottery_num;
}
//所有实物中奖信息和下载抽奖中奖信息
function get_award_all($aid){
	global $model;
	global $redis;		
	global $prefix;		
	$award_list = $redis -> getlist("{$prefix}:{$aid}:lottery_record_all");
	$down_record_all = $redis -> getlist("{$prefix}:{$aid}:down_record_all");
	if(!$award_list || !$down_record_all){
		$option = array(
			'where' => array(//查询条件
				'a.status' => 1,
				'a.aid' => $aid,
				'b.aid' => $aid,
			),
			'table' => 'xy2_draw_award AS a',
			'field' => 'a.username,a.prizename,b.type',
			'order' => 'a.id asc',
			'join' => array(
				'xy2_draw_prize AS b' => array(
						'on' => array('a.pid', 'b.id')
				)
			)
		);		
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$award_list = array();
		$down_record_all = array();
		$i = 0;
		$ii = 0;
		foreach((array)$kind_award as $k => $v){
			if($v['type'] == 2){
				$down_record_all[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
				$down_record_all[$k]['prizename'] = $v['prizename'];	
				$ii++;
			}else{
				$award_list[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
				$award_list[$k]['prizename'] = $v['prizename'];
				$i++;
			}
		}
		unset($kind_award);
		$redis -> setlist_sec("{$prefix}:{$aid}:down_record_all",$down_record_all,30*60);
		$redis -> setlist_sec("{$prefix}:{$aid}:lottery_record_all",$award_list,30*60);
	}else{
		$i = 0;
		foreach($award_list as $k => $v){
			$award_list[$k] = json_decode($v,true);
			$i++;
		}
		$ii =0;
		foreach($down_record_all as $k => $v){
			$down_record_all[$k] = json_decode($v,true);
			$ii++;
		}	
	}	
	return array($award_list,$down_record_all,$i,$ii);	
}
//安装获得一次抽奖机会
function add_down_lottery_num($uid,$aid){
	global $model;
	global $redis;		
	global $prefix;		
	$time = time();
	$where = array(
		'uid' => $uid,
		'aid' => $aid,
	);
	$res = get_user_info_new($uid,$aid,$prefix,"xy2_draw_userinfo");
	$key = "{$prefix}:{$aid}:rest_down_num:".$uid;		
	if($res){
		$data_update = array(
			'silver_num' => array('exp',"`silver_num`+1"),
			'update_tm' => $time,
			'__user_table' => 'xy2_draw_userinfo'
		);
		$ret = $model -> update($where,$data_update,'lottery/lottery');	
		if($ret){
			$res = $redis->setx('incr',$key,+1);	
		}		
	}else{
		$data = array(
			'uid' => $uid,
			'aid' => $aid,
			'username' => $_SESSION['USER_NAME'],
			'silver_num' => 1,
			'update_tm' => $time,
			'create_tm' => $time,
			'__user_table' => 'xy2_draw_userinfo'
        );
        $ret =  $model->insert($data,'lottery/lottery');
		if($ret){
			$redis->set($key,1,15*60);	
		}		
	}	
	return $ret;
}