<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "smashed_egg";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!$_GET['ap_id'] && !ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$activity_host = 'http://m.test.anzhi.com';
}else{
	$activity_host = 'http://promotion.anzhi.com';
}
if($_GET['stop'] != 1 && !$_GET['types']){
	$res = activity_is_stop($active_id);
	if(!$res){
		if( isset($_GET['new']) ){
			$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid."&new=1";
		}else{
			$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		}
		header("Location: {$url}");
	}
}
$time = time();
//跑马灯轮播前10条排行榜数据
function get_top10_ranking($aid){
	$config_arr = array("恭喜用户user，累积砸蛋deduction_lottery_num次，厮杀至榜首","恭喜用户user，累积砸蛋deduction_lottery_num次，高居排行榜第二","恭喜用户user，累积砸蛋deduction_lottery_num次，暂居第三","恭喜用户user，累积砸蛋deduction_lottery_num次，登至排行榜第四","恭喜用户user，累积砸蛋deduction_lottery_num次，杀进排行榜前五","恭喜用户user，累积砸蛋deduction_lottery_num次，冲入排行榜第六","恭喜用户user，累积砸蛋deduction_lottery_num次，拼杀至排行榜第七","恭喜用户user，累积砸蛋deduction_lottery_num次，奋战入排行榜第八","恭喜用户user，累积砸蛋deduction_lottery_num次，冲入排行榜第九","恭喜用户user，累积砸蛋deduction_lottery_num次，挤入排行榜第十");
	global $model;
	$option = array(
		'table' => 'integral_userinfo',
		'where' => array(
			'aid' => $aid,
			'deduction_lottery_num' => array('exp',' >=500')
		),
		'field' => 'uid,deduction_lottery_num,username',
		'order' => 'deduction_lottery_num desc',
		'limit' => 10,
		'cache_time' => 10*60
	);
	$list_arr = $model->findAll($option,'lottery/lottery');	
	$list = array();	
	foreach($list_arr as $k=>$v){
		if(empty($v['username'])){
			$username = get_by_username($v['uid'],$aid);
			$username = str_replace_cn_new($username, 1, -2 );
		}else{
			$username = str_replace_cn_new($v['username'], 1, -2 );
		}			
		$search   = array("user","deduction_lottery_num");
		$replace  = array($username,$v['deduction_lottery_num']);
		$msgs = str_replace($search,$replace,$config_arr[$k]);			
		$list[$k]['msg'] = trim($msgs);
		$list[$k]['deduction_lottery_num'] = $v['deduction_lottery_num'];	
		$list[$k]['username'] = $username;	
	}
	//var_dump($list);exit;
	return $list;
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
			'__user_table' => 'integral_userinfo'
		);
		$where = array(
				'uid' => $uid,
				'aid' => $aid,
		);
		$model->update($where, $data,'lottery/lottery');	
	}
	return $ret;
}
//@剩余抽奖次数
function get_rest_lottery($uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$lottery_num = $redis->get($lottery_num_key);
	if($lottery_num === null ){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'integral_userinfo',
			'field' => 'lottery_num,deduction_lottery_num,rebate_num,login_num',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		//登录赠送砸蛋次数
		$login_num_key = "{$prefix}:".$active_id.":login_lottery_num:".$uid.":".date("Ymd");
		$login_num =  $redis->get($login_num_key);
		$lottery_num = intval($rest_list['lottery_num'])-intval($rest_list['deduction_lottery_num'])+intval($rest_list['rebate_num'])+intval($rest_list['login_num'])+$login_num;
		$redis->set($lottery_num_key,intval($lottery_num),15*60);	
	}	
	return $lottery_num;			
}
//每天登录送锤子
function login_add_lottery($uid){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$login_num_key = "{$prefix}:".$active_id.":login_lottery_num:".$uid.":".date("Ymd");
	$login_num =  $redis->get($login_num_key);
	if($login_num === null){
		$redis->set($login_num_key,5,86400);		
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'integral_userinfo',
			'field' => 'lottery_num,deduction_lottery_num,rebate_num,login_num',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$lottery_num_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
		if($rest_list){
			$lottery_num = intval($rest_list['lottery_num'])+intval($rest_list['rebate_num'])+5-intval($rest_list['deduction_lottery_num'])+intval($rest_list['login_num']);
			$redis->set($lottery_num_key,intval($lottery_num),15*60);	
		}else{
			$data = array(
				'uid' => $uid,
				'aid' => $active_id,
				'username' => $_SESSION['USER_NAME'],
			);
			add_user_new($data,$time,"{$prefix}","integral_userinfo");
			$redis->set($lottery_num_key,5,15*60);
		}
	}
}

//用户已用抽奖次数
function save_deduction_num($uid,$num){
	global $model;	
	global $active_id;		
	global $time;	
	global $prefix;		
	global $redis;		
	$where = array(
		'uid' => $uid,
		'aid' => $active_id 
	);
	$data_update = array(
		'deduction_lottery_num' => array('exp',"`deduction_lottery_num`+{$num}"),
		'update_tm' => $time,
		'__user_table' => 'integral_userinfo'
	);
	$login_num_key = "{$prefix}:".$active_id.":login_lottery_num:".$uid.":".date("Ymd");
	$login_num = $redis->get($login_num_key);
	if($num == 10){
		$data_update['rebate_num'] =  array('exp',"`rebate_num`+1");
		if($login_num > 0) $data_update['login_num'] =  array('exp',"`login_num`+{$login_num}");
		$rebate_num = 1;
		$redis->set($login_num_key,0,86400);
	}else if($num == 100){
		$data_update['rebate_num'] =  array('exp',"`rebate_num`+20");
		if($login_num > 0) $data_update['login_num'] =  array('exp',"`login_num`+{$login_num}");
		$rebate_num = 20;
		$redis->set($login_num_key,0,86400);
	}else{
		if($login_num > 0){
			$redis->setx('incr',$login_num_key,-1);
			$data_update['login_num'] =  array('exp',"`login_num`+1");
		}	
	}
	$model -> update($where,$data_update,'lottery/lottery');
	$lottery_key = "{$prefix}:".$active_id.":res_lottery_num:".$uid;
	$now_num = $redis->setx('incr',$lottery_key,$rebate_num);	
	//使用次数
	$deduction_num_key = "{$prefix}:".$active_id.":deduction_lottery_num:".$uid;
	$redis->setx('incr',$deduction_num_key,$num);	
}
//分页
function get_page($data,$limit){
	$total = count($data);
	$page = $_GET['page'] ? $_GET['page'] : 1;
	$new_page = ceil($total/$limit);//总页数
	//如果输入页数据大于总页数 用最后一页
	//如果输入页数据小于总页数 用第一页
	if( $new_page < $page || $page < 1 ){
		return false;
	}
	$offset = ($page - 1) * $limit;		
	$ret =  array_slice($data,$offset,$limit);
	return array($ret,$new_page);
}
//用户幸运值
function get_luk($uid){
	global $model;	
	global $active_id;		
	global $time;	
	global $prefix;		
	global $redis;	
	$luk_key = "{$prefix}:".$active_id.":luk:".$uid;
	$luk_num = $redis->get($luk_key);
	if($luk_num === null ){
		$luk_arr = array(
			3 => 1,
			4 => 3,
			5 => 10,
			6 => 20,
			7 => 50,
			8 => 100,
		);	
		$where = array(
			'a.uid' => $uid,
			'a.aid' => $active_id, 
			'b.aid' => $active_id,
			'b.level' => array('exp',">2"),
			'b.status' => 1	
		);
		$option = array(
			'table' => 'valentine_draw_award as a',
			'where' => $where,
			'field' => 'a.uid,a.username,b.name,b.level',
			'join' => array(
				'valentine_draw_prize as b' => array(
					'on' => array('a.pid','b.id')
				)
			)
		);		
		$list = $model->findAll($option,'lottery/lottery');	
		$i = 0;
		foreach($list as $k =>$v){
			$i = $i+$luk_arr[$v['level']];
		}

		$data = array(
			'integral_num' => $i,
			'__user_table' => 'integral_userinfo'
		);
		$where = array(
				'uid' => $uid,
				'aid' => $active_id,
		);
		$model->update($where, $data,'lottery/lottery');	
		
		$option = array(
			'where' => $where,
			'table' => 'integral_userinfo',
			'field' => 'deduction_integral',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$luk_num = $i-$rest_list['deduction_integral'];
		$redis->set($luk_key,intval($luk_num),15*60);		
	}
	return $luk_num;
}

function save_luk($uid,$position){
	global $active_id;	
	global $prefix;	
	global $model;			
	if($position == 11){
		$luk_num = 100;
	}else if($position == 12){
		$luk_num = 200;
	}else if($position == 13){
		$luk_num = 300;
	}else if($position == 14){
		$luk_num = 500;
	}else if($position == 15){
		$luk_num = 1000;
	}else if($position == 16){
		$luk_num = 2000;
	}	
	$data = array(
		'deduction_integral' => array('exp',"`deduction_integral`+{$luk_num}"),
		'__user_table' => 'integral_userinfo'
	);
	$where = array(
			'uid' => $uid,
			'aid' => $active_id,
	);
	$res = $model->update($where, $data,'lottery/lottery');	
}

function get_lottrey_pid(){
	global $active_id;	
	global $model;			
	$where = array(	'aid' => $active_id	);	
	$option = array(
		'table' => 'valentine_draw_prize',
		'where' => $where,
		'field' => 'id,level',
		'cache_time' => 15*60
	);		
	$pid = $model->findAll($option,'lottery/lottery');	
	$pid_arr = array();
	foreach($pid as $k=>$v){
		$pid_arr[$v['id']] = intval($v['level']);
	}
	return $pid_arr;
}

function get_practicality_pid($level){
	global $model;	
	global $active_id;			
	$option = array(
		'where' => array(
			'aid' => $active_id,
			'level' => $level
		),
		'table' => 'valentine_draw_prize',
		'field' => 'id',
		'cache_time' => 15*60		
	);
	$rest = $model->findOne($option,'lottery/lottery');		
	return $rest['id'];	
}

function get_deduction_lottery_num($uid){
	global $model;	
	global $active_id;		
	global $prefix;		
	global $redis;		
	$deduction_num_key = "{$prefix}:".$active_id.":deduction_lottery_num:".$uid;
	$deduction_num =  $redis->get($deduction_num_key);
	if($deduction_num === null ){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $active_id
			),
			'table' => 'integral_userinfo',
			'field' => 'deduction_lottery_num',
		);
		$rest_list = $model->findOne($option,'lottery/lottery');	
		$deduction_num = $rest_list['deduction_lottery_num'];
		$redis->set($deduction_num_key,intval($deduction_num),15*60);
	}
	return 	$deduction_num;
}