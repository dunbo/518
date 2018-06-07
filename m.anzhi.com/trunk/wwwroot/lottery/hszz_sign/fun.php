<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

$prefix = "hszznew";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
if(!isset($active_id)){
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
		$active_id = 450;
	}else{
		$active_id = 447;
	}	
	$url = $center_url."http://promotion.anzhi.com/lottery/hszz_sign/index.php?aid=".$active_id."&sid=".$sid;
	header("Location: {$url}");
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];

if($_GET['stop'] != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $center_url."http://promotion.anzhi.com/lottery/hszz_sign/index.php?stop=1&aid=".$active_id."&sid=".$sid;
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
				'sign_status' => 0,
				'time' => strtotime(date("Y-m-d",$start)),
			),
			date("Y-m-d",$start+86400) => array(
				'num' => 2,			
				'sign_status' => 0,			
				'time' => strtotime(date("Y-m-d",$start+86400)),
			),
			date("Y-m-d",$start+86400*2) => array(
				'num' => 3,				
				'sign_status' => 0,			
				'time' => strtotime(date("Y-m-d",$start+86400*2)),
			),
			date("Y-m-d",$start+86400*3) => array(
				'num' => 4,								
				'sign_status' => 0,			
				'time' => strtotime(date("Y-m-d",$start+86400*3)),
			),
			date("Y-m-d",$start+86400*4) => array(
				'num' => 5,				
				'sign_status' => 0,				
				'time' => strtotime(date("Y-m-d",$start+86400*4)),
			),
			date("Y-m-d",$start+86400*5) => array(
				'num' => 6,									
				'sign_status' => 0,				
				'time' => strtotime(date("Y-m-d",$start+86400*5)),
			),
			date("Y-m-d",$start+86400*6) =>array(
				'num' => 7,									
				'sign_status' => 0,			
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
	// var_dump("{$prefix}:{$aid}_rest_lottery_num:".$uid);
	// $redis->set("{$prefix}:{$aid}_rest_lottery_num:".$uid,0,10*86400);
	$lottery_num = $redis->get("{$prefix}:{$aid}_rest_lottery_num:".$uid);
	// var_dump($lottery_num);die;
	if($lottery_num === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid
			),
			'table' => 'royal_war_draw_userinfo',
		);
		$userinfo = $model->findOne($option,'lottery/lottery');	
		// echo $model->getLatestSql();
		//剩余抽奖次数
		$lottery_num = $userinfo['draw_data_num']-$userinfo['deduction_num'];
		if($lottery_num < 0){
			$lottery_num = 0;
		}
		$redis->set("{$prefix}:{$aid}_rest_lottery_num:".$uid,intval($lottery_num),10*86400);		
		// echo $lottery_num;
	}	
	return $lottery_num;		
}

function get_sign_down_share_num($prefix,$aid,$uid,$from,$time){
	global $redis;
	global $model;	
	$tm_con = get_config($prefix,$aid,$uid);
	$i = 0;//签到次数
	$start_tm="";
	foreach($tm_con as $k => $v){
		$i++;
		if($time>$v['time'] && $time<($v['time']+86400)){
			$start_tm=$v['time'];
			 break;
		}

	}
	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $aid,
		),
		'table' => 'royal_war_user_sign',
	);
	$option['where']['create_tm']=array('exp','<'.($start_tm+86400).' and create_tm>='.$start_tm);
	$royal_sign_new = $model->findOne($option,'lottery/lottery');
	if($royal_sign_new){
		return 1;
	}
	$option_two = array(
		'where' => array(
			'aid' => $aid,
			'imsi' => $_SESSION['USER_IMSI'],
		),
		'table' => 'royal_war_draw_userinfo',
	);
	$royal_sign = $model->findAll($option_two,'lottery/lottery');
	if(count($royal_sign)>=2){
		$a=0;
		foreach($royal_sign as $k=>$v){
			if($v['uid']!=$uid){
				$a++;
			}
		}
		if($a>=2){
			return 2;
		}
	}


	$new_data = array(
		'uid' => $uid,
		'aid' => $aid,
		'create_tm' => $time,
		'__user_table' => 'royal_war_user_sign'
    );		
    $ret =  $model->insert($new_data,'lottery/lottery');
    if($ret){
    	$data = array(
		'uid' => $uid,
		'aid' => $aid,
		);
		add_user($prefix,$data,$time,1);
    }	
}

function get_sign_status($prefix,$aid,$uid,$time){
	global $redis;
	global $model;	
	$tm_con = get_config($prefix,$aid,$uid);
	$i = 0;//签到次数
	$start_tm="";
	foreach($tm_con as $k => $v){
		$i++;
		if($time>$v['time'] && $time<($v['time']+86400)){
			$start_tm=$v['time'];
			 break;
		}
	}
	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $aid,
		),
		'table' => 'royal_war_user_sign',
	);
	$option['where']['create_tm']=array('exp','>'.($start_tm).' and create_tm<='.($start_tm+86400));
	$royal_sign = $model->findOne($option,'lottery/lottery');
	// echo $model->getLatestSql();
	// $royal_sign=$model->query($model->getLatestSql());
	// var_dump($royal_sign);
	if($royal_sign){
		return 1;
	}else{
		return 2;
	}
}
//获取签到是哪几天
function get_sign_days($prefix,$aid,$uid){
	global $model;	
	$tm_con = get_config($prefix,$aid,$uid);
	$i = 0;//签到次数
	// $start_tm="";
	$sign_days=array();
	$time=time();
	foreach($tm_con as $k => $v){
		$i++;
		if($time<$v['time']){
		    $sign_days[$i][]=$i;
			$sign_days[$i][]=3;
		}else{
			$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid,
			),
			'table' => 'royal_war_user_sign',
			);
			$option['where']['create_tm']=array('exp','<'.($v['time']+86400).' and create_tm>='.($v['time']));
			$sign_day = $model->findOne($option,'lottery/lottery');
			if(!empty($sign_day)){
				$sign_days[$i][]=$i;
				$sign_days[$i][]=1;
			}else{
				$sign_days[$i][]=$i;
				$sign_days[$i][]=2;
			}
		}
		// echo $model->getLatestSql();echo "<br>";
		// $sign_days[$i] =$sign_day;
	}
	return $sign_days;
}
//添加用户、修改用户信息
function add_user($prefix,$data,$time,$num=0){
	global $model;
	global $redis;		
	$option = array(
		'where' => array(
			'uid' => $data['uid'],
			'aid' => $data['aid']
		),
		// 'table' => 'valentine_draw_userinfo',
		'table' => 'royal_war_draw_userinfo',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $new_data = array(
			'uid' => $data['uid'],
			'username' => $_SESSION['USER_NAME'],
			'imsi' => $_SESSION['USER_IMSI'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],
			'update_tm' => $time,
			'__user_table' => 'royal_war_draw_userinfo',

        );
		// if($data['draw_data_num']){
		// 	 $new_data['draw_data_num'] = $data['draw_data_num'];
		// }else{
			 $new_data['draw_data_num'] = $userinfo['draw_data_num']+$num;
		// }
        $where = array(
			'uid' => $data['uid'],
			'aid' =>$data['aid'],
        );
        $ret =  $model->update($where, $new_data,'lottery/lottery');
		if($ret){
			//剩余抽奖次数
			$rest = intval($num+$userinfo['draw_data_num']-$userinfo['deduction_num']);
			// $rest = intval($new_data['draw_data_num']-$userinfo['deduction_num']);
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
			'imsi' => $_SESSION['USER_IMSI'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],			
			'update_tm' => $time,
			'create_tm' => $time,
			'draw_data_num' => $num,
			'__user_table' => 'royal_war_draw_userinfo'
        );		
        $ret =  $model->insert($new_data,'lottery/lottery');
		// var_dump($model->getLatestSql());
		if($ret){
			// var_dump($num);
			$redis->set("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'],$num,10*86400);
			// var_dump($redis->get("{$prefix}:{$data['aid']}_rest_lottery_num:".$data['uid'],$num,10*86400));	
			
		}
    }
    //存redis待定
    $redis->set("{$prefix}:{$data['aid']}_userinfo:".$data['uid'],$new_data,86400*10);
	return 	$ret;
}
//所有实物中奖信息
function get_award_all_top30($aid,$prefix,$table){
	global $model;
	global $redis;		
	// $redis -> rpop("{$prefix}:{$aid}_draw_award_top30");
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
