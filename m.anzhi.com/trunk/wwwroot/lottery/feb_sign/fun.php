<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "feb";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
if(!isset($active_id)){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$active_id = 424;
	}else{
		$active_id = 410;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}_sign/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}_sign/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
//获取活动配置
function get_config($prefix,$aid,$uid){
	global $model;
	global $redis;	
	$tm_config = $redis->get("{$prefix}:{$aid}_tm_config:".$uid);	
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
				'name' => '初一',
				'sign_status' => 0,
				'share_status' => 0,
				'down_status' => 0,
				'turn_aid' => 402,
				'time' => strtotime(date("Y-m-d",$start)),
			),
			date("Y-m-d",$start+86400) => array(
				'num' => 2,
				'name' => '初二',				
				'sign_status' => 0,
				'share_status' => 0,	
				'down_status' => 0,				
				'turn_aid' => 402,				
				'time' => strtotime(date("Y-m-d",$start+86400)),
			),
			date("Y-m-d",$start+86400*2) => array(
				'num' => 3,
				'name' => '初三',					
				'sign_status' => 0,
				'share_status' => 0,	
				'down_status' => 0,		
				'turn_aid' => 402,				
				'time' => strtotime(date("Y-m-d",$start+86400*2)),
			),
			date("Y-m-d",$start+86400*3) => array(
				'num' => 4,
				'name' => '初四',									
				'sign_status' => 0,
				'share_status' => 0,	
				'down_status' => 0,		
				'turn_aid' => 403,				
				'time' => strtotime(date("Y-m-d",$start+86400*3)),
			),
			date("Y-m-d",$start+86400*4) => array(
				'num' => 5,
				'name' => '初五',					
				'sign_status' => 0,
				'share_status' => 0,	
				'down_status' => 0,		
				'turn_aid' => 403,				
				'time' => strtotime(date("Y-m-d",$start+86400*4)),
			),
			date("Y-m-d",$start+86400*5) => array(
				'num' => 6,
				'name' => '初六',									
				'sign_status' => 0,
				'share_status' => 0,	
				'down_status' => 0,			
				'turn_aid' => 403,				
				'time' => strtotime(date("Y-m-d",$start+86400*5)),
			),
			date("Y-m-d",$start+86400*6) =>array(
				'num' => 7,
				'name' => '初七',									
				'sign_status' => 0,
				'share_status' => 0,	
				'down_status' => 0,		
				'turn_aid' => 403,				
				'time' => strtotime(date("Y-m-d",$start+86400*6)),
			),
		);
		$redis->set("{$prefix}:{$aid}_tm_config:".$uid,$tm_config,10*86400);
	}
	return $tm_config;			
}
//获取抽奖次数
function get_lottery_num($prefix,$aid,$uid){
	global $model;
	global $redis;		
	$lottery_num = $redis->get("{$prefix}:{$aid}_rest_lottery_num:".$uid);
	if($lottery_num === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid
			),
			'table' => 'valentine_draw_userinfo',
		);
		$userinfo = $model->findOne($option,'lottery/lottery');	
		//剩余抽奖次数
		$lottery_num = $userinfo['draw_data_num']-$userinfo['deduction_num'];
		if($lottery_num < 0){
			$lottery_num = 0;
		}
		$redis->set("{$prefix}:{$aid}_rest_lottery_num:".$uid,intval($lottery_num),10*86400);		
	}	
	return $lottery_num;		
}
function get_sign_down_share_num($prefix,$aid,$uid,$from,$time){
	global $redis;		
	$tm_con = get_config($prefix,$aid,$uid);		
	if($tm_con[date('Y-m-d',$time)]['num']){	
		$tm_con[date('Y-m-d',$time)][$from] = 1;
		$redis->set("{$prefix}:{$aid}_tm_config:".$uid,$tm_con,86400*10);
	}		
	$i = 0;//签到次数
	$ii = 0;//下载次数	
	$iii = 0;//分享次数
	foreach($tm_con as $k => $v){
		if($v['num']){
			if($v['sign_status'] == 1){
				$i++;
			}
			if($v['down_status'] == 1){
				$ii++;
			}
			if($v['share_status'] == 1){
				$iii++;
			}
		}
	}
	$sign_num = $i > 7 ? 7 : $i;
	$down_num = $ii > 7 ? 7 : $ii;		
	$share_num = $iii > 7 ? 7 : $iii;
	$draw_data_num = intval($down_num+$sign_num+$share_num);
	$data = array(
		'draw_data_num' => $draw_data_num > 21 ? 21 : $draw_data_num,
		'uid' => $uid,
		'aid' => $aid,
	);
	add_user($prefix,$data,$time);	
}
//添加用户、修改用户信息
function add_user($prefix,$data,$time){
	global $model;
	global $redis;		
	$option = array(
		'where' => array(
			'uid' => $data['uid'],
			'aid' => $data['aid']
		),
		'table' => 'valentine_draw_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $new_data = array(
			'uid' => $data['uid'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],
			'update_tm' => $time,
			'__user_table' => 'valentine_draw_userinfo'
        );
		if($data['draw_data_num']){
			 $new_data['draw_data_num'] = $data['draw_data_num'];
		}else{
			 $new_data['draw_data_num'] = $userinfo['draw_data_num'];
		}
        $where = array(
			'uid' => $data['uid'],
			'aid' =>$data['aid'],
        );
        $ret =  $model->update($where, $new_data,'lottery/lottery');
		if($ret){
			//剩余抽奖次数
			$rest = intval($new_data['draw_data_num']-$userinfo['deduction_num']);
			if($rest < 0){
				$rest = 0;
			}
			$redis->set("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'],$rest,10*86400);			
		}
    }else {//新增
        $new_data = array(
			'uid' => $data['uid'],
			'aid' => $data['aid'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],			
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => 'valentine_draw_userinfo'
        );
		if($data['draw_data_num']){
			 $new_data['draw_data_num'] = $data['draw_data_num'];
		}		
        $ret =  $model->insert($new_data,'lottery/lottery');
		if($ret){
			$redis->set("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'],intval($data['draw_data_num']),10*86400);	
		}
    }
    $redis->set("{$prefix}:{$data['aid']}_userinfo:".$data['uid'],$new_data,86400*10);
	return 	$ret;
}
//所有实物中奖信息
function get_award_all_top30($aid,$prefix,$table){
	global $model;
	global $redis;		
	$award_list = $redis -> getlist("{$prefix}:{$aid}_draw_award_top30");
	if(!$award_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'aid' => $aid
			),
			'table' => $table,
			'field' => 'username,prizename',
			'order' => 'id desc',
			'limit' => 30
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$award_list = array();
		foreach((array)$kind_award as $k => $v){
			$award_list[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
			$award_list[$k]['prizename'] = $v['prizename'];
		}
		unset($kind_award);
		$redis -> setlist("{$prefix}:{$aid}_draw_award_top30",$award_list,30*60);
	}else{
		foreach($award_list as $k => $v){
			$award_list[$k] = json_decode($v,true);
		}	
	}	
	return $award_list;
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
