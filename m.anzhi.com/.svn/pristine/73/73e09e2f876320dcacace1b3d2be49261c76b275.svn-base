<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"seckill";
$active_id	=	$_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit	检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];

$model = new GoModel();
if($configs['is_test'] == 1 ) {
	$time  = get_now_time();
}else {
	$time  = time();
}

//获取host
$activity_host = $configs['activity_url'];

if($_GET['stop'] != 1 ) {
	$res = activity_is_stop($active_id);
	if(!$res) {
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id;
		header("Location: {$url}");
	}
}

//获取活动配置
function get_level_id(){
	global $model;
	global $redis;
	global $prefix;
	global $active_id;	
	$level_id_config_key = "{$prefix}:".$active_id.":level_id_config";
	$config_arr = $redis->get($level_id_config_key);
	if($config_arr === null ){	
		$option = array(
			'where' => array(
				'id' => $active_id,
			),
			'table' => 'sj_activity',
			'field' => 'start_tm',
		);
		$activity = $model->findOne($option);	
		$start_tm = $activity['start_tm'];
		$date = date("Ymd",$start_tm);
		$date_1 = date("Ymd",$start_tm+86400);
		$date_2 = date("Ymd",$start_tm+2*86400);
		$config_arr = array(
			$date."_00" => array(
				"level_id" => array(1,2,3,4),
				"azb_mount" => 10,
			),
			$date."_08" => array(
				"level_id" => array(5,6,7,8),
				"azb_mount" => 20,
			),
			$date."_12" => array(
				"level_id" => array(9,10,11,12),
				"azb_mount" => 30,
			),
			$date."_16" => array(
				"level_id" => array(13,14,15,16),
				"azb_mount" => 20,
			),
			$date."_20" => array(
				"level_id" => array(17,18,19,20),
				"azb_mount" => 10,
			),
			$date_1."_00" => array(
				"level_id" => array(21,22,23,24),
				"azb_mount" => 10,
			),
			$date_1."_08" => array(
				"level_id" => array(25,26,27,28),
				"azb_mount" => 20,
			),
			$date_1."_12" => array(
				"level_id" => array(29,30,31,32),
				"azb_mount" => 30,
			),
			$date_1."_16" => array(
				"level_id" => array(33,34,35,36),
				"azb_mount" => 20,
			),
			$date_1."_20" => array(
				"level_id" => array(37,38,39,40),
				"azb_mount" => 10,
			),
			$date_2."_00" => array(
				"level_id" => array(41,42,43,44),
				"azb_mount" => 10,
			),
			$date_2."_08" => array(
				"level_id" => array(45,46,47,48),
				"azb_mount" => 20,
			),
			$date_2."_12" => array(
				"level_id" => array(49,50,51,52),
				"azb_mount" => 30,
			),
			$date_2."_16" => array(
				"level_id" => array(53,54,55,56),
				"azb_mount" => 50,
			),
			$date_2."_20" => array(
				"level_id" => array(57,58,59,60),
				"azb_mount" => 10,
			),
		);
		$redis->set($level_id_config_key,$config_arr,86400*10);
	}
	return $config_arr;			
}

function get_now_h($time){
	$H = date("H",$time);
	if($H >= 0 and $H < 8){
		$arr = array(
			"now_h" => "00",
			"next_tab" => "08",
			"next_tab2" => "12",
		);
	}else if($H >= 8 and $H < 12){
		$arr = array(
			"now_h" => "08",
			"next_tab" => "12",
			"next_tab2" => "16",
		);		
	}else if($H >= 12 and $H < 16){
		$arr = array(
			"now_h" => "12",
			"next_tab" => "16",
			"next_tab2" => "20",
		);
	}else if($H >= 16 and $H < 20){
		$arr = array(
			"now_h" => "16",
			"next_tab" => "20",
			"next_tab2" => "00",
		);
	}else if($H >= 20){
		$arr = array(
			"now_h" => "20",
			"next_tab" => "00",
			"next_tab2" => "08",
		);
	}	
	return $arr;
}
//奖品配置 未完成
function get_prize_config($day,$time){
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	$h_arr = get_now_h($time);
	$H = $h_arr['now_h'];
	$prize_key	=	"{$prefix}:{$active_id}_prize_config:{$day}:{$H}";
	$prize_config = $redis->get($prize_key);
	if( empty($prize_config) ) {
		$config_arr = get_level_id();
		$level_ids = $config_arr[$day.'_'.$H]['level_id'];
		$option = array(
			'where' => array(
				'aid'	 =>	$active_id,
				'level'	 =>	$level_ids,	
				'status' =>	1,
			),
			'table' => 'valentine_draw_prize',
			'field' => 'id,level,name,num,prize_num,type',
			'order' => 'level asc',
		);
		$prize_config	=	$model->findAll($option,'lottery/lottery');
		foreach($prize_config as $k =>$v){
			if($k == 0){
				$prize_config[$k]['azb_mount'] = $config_arr[$day.'_'.$H]['azb_mount'];
				$prize_config[$k]['level_ids'] = implode(",",$level_ids);
			}
		}
		$redis -> set($prize_key, $prize_config,10*60);
	}
	foreach($prize_config as $k =>$v){
		$now_num = $redis->get("{$prefix}:{$active_id}:prize_num:".$v['id']);
		$prize_config[$k]['num'] = isset($now_num) ? intval($now_num) : intval($v['num']);
		$prize_config[$k]['prize_num'] = intval($v['prize_num']);
		$prize_config[$k]['res_num'] = intval($v['prize_num']-$v['num']);
		$prize_config[$k]['per'] = "剩余" . round(($v['num']/$v['prize_num'])*100,2)."%";
		if($v['type'] == 4){
			$prize_config[$k]['level'] = 100;
			$prize_config[$k]['name'] = '现金礼券';
		}
	}
	return $prize_config;
}

/**
 * 我的领取的礼券记录
 * @param int $uid
 * @param int $aid
 * @param int $prefix
 * @param str $table
 * @return array
 */

//获取用户安智币
function get_azmoney($uid){
	global $prefix;
	global $position;
	load_helper('task');
	$task_client = get_task_client();
	$new_array=array(
		'workerType'=>	1,
		'uid'		=>	$uid
	);
	$the_award = $task_client->do('anzhi_lottery_money', json_encode($new_array));
	return json_decode($the_award,true);
}
//消费安智币
function azb_consume($uid,$pwd,$azbAmount){
	global $active_id;	
	global $sid;	
	global $time;	
	load_helper('task');
	$task_client = get_task_client();
	$new_array=array(
		'workerType'=>	2,
		'uid'		=>	$uid,
		'appkey'	=>	'1392365303Jy1R97taJfdtops8Cxum',
		'orderDes'	=>	'双11整点夺宝活动',
		'payPwd'	=>	$pwd,
		'azbAmount'	=>	$azbAmount,
	);
	$the_award = $task_client->do('anzhi_lottery_money', json_encode($new_array));
	$ret = json_decode($the_award,true);
	//消费日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'azbAmount' => $azbAmount,//安智币
		'uid'=>$uid,
		'key' => 'azb_consume',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	if($ret['code'] == 1){
		//抽奖成功日志
		$log_data = array(
			"imsi"			=>	$_SESSION['USER_IMSI'],
			"device_id"		=>	$_SESSION['DEVICEID'],
			"activity_id"	=>	$active_id,
			"ip"			=>	$_SERVER['REMOTE_ADDR'],
			"sid"			=>	$sid,
			"time"			=>	$time,
			"user"			=>	$_SESSION['USER_NAME'],
			'azbAmount' => $azbAmount,
			'uid'			=>	$uid,
			'key'			=>	'azb_consume_success',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
	}
	return $ret;
}
//抽奖
function lottery_do($uid,$position,$gift_pkg){
	global $active_id;	
	global $prefix;	
	load_helper('task');
	$task_client = get_task_client();	
	$new_array=array(
		'uid'		=>	$uid,
		'aid'		=>	$active_id,
		'username'	=>	$_SESSION['USER_NAME'],
		'prefix'	=>	$prefix,
		'position'	=>	$position,
		'gift_pkg'	=>	$gift_pkg,
		'activityName' => '双11整点夺宝活动',
	);
	$the_award = $task_client->do('anzhi_lottery_money', json_encode($new_array));	
	if($the_award == '-1'){
		$ret_arr  = array(
			'code' => 0,
			'msg' => '未中奖'
		);
		return $ret_arr;
	}else{
		return json_decode($the_award,true);
	}
}
function get_prize_num($level_ids){
	global $redis;
	global $prefix;
	global $active_id;
	$prize_now_num_key	=	"{$prefix}:{$active_id}_prize_now_num:{$level_ids}";
	$prize_now_num = $redis->get($prize_now_num_key);
	if($prize_now_num === null){
		$redis->set($prize_now_num_key,1,10*86400);
		$now_num =  1;
	}else{
		$now_num = $redis -> setx('incr',$prize_now_num_key,+1);
	}
	if($now_num ==1500){
		$userinfo = prize_user_config($level_ids);
		add_test_award($userinfo['uid'],$userinfo['username'],$level_ids);
	}
}
function prize_user_config($level_ids){
	$con_arr = array(
		'1,2,3,4' => array(
			'uid' => '20151222164540x50cMae6Mx',
			'username' => 'az84607163',
		),
		'5,6,7,8' => array(
			'uid' => '20131015143305u7C9xiLe1L',
			'username' => 'aztt13716490',
		),
		'9,10,11,12' => array(
			'uid' => '20140717113204Q2eDEB22U7',
			'username' => 'darkzyl',
		),
		'13,14,15,16' => array(
			'uid' => '201411121551270uP6zqbTRP',
			'username' => 'azjxyz110',
		),
		'17,18,19,20' => array(
			'uid' => '20161027154025252EajojB9',
			'username' => 'az132312049',
		),
		'21,22,23,24' => array(
			'uid' => '20160331103802I0wxhvMapI',
			'username' => 'az100681553',
		),
		'25,26,27,28' => array(
			'uid' => '20160623205302pj8SFz1e8g',
			'username' => 'az114224977',
		),
		'29,30,31,32' => array(
			'uid' => '201504281832037Dr9487aSz',
			'username' => 'az53484482',
		),
		'33,34,35,36' => array(
			'uid' => '20141106140906i2ouEtLdou',
			'username' => 'az30282450',
		),
		'37,38,39,40' => array(
			'uid' => '20161027154825739rz2K1iP',
			'username' => 'votreclemence',
		),
		'41,42,43,44' => array(
			'uid' => '20161027155333n99rzw5GHs',
			'username' => 'BO7452188',
		),
		'45,46,47,48' => array(
			'uid' => '20161027155710A2MHey2QGu',
			'username' => 'liangli04',
		),
		'49,50,51,52' => array(
			'uid' => '201610271557515k0qG7KE8F',
			'username' => 'heney0y',
		),
		'53,54,55,56' => array(
			'uid' => '20161027155851M4b749c8tu',
			'username' => 'ab7756ri',
		),
		'57,58,59,60' => array(
			'uid' => '2014102416114793sy6m3S5n',
			'username' => 'az28785984',
		),
	);
	return $con_arr[$level_ids];
}
function add_test_award($uid,$username,$level_ids){
	global $model;
	global $active_id;
	global $time;
	global $redis;
	$option = array(
		'where' => array(
			'aid' => $active_id,
			'probability' => 0,
			'level' => explode(',',$level_ids)
		),
		'table' => 'valentine_draw_prize',
		'field' => 'id,prize_num,name,level',
	);
	$draw_prize = $model->findOne($option,'lottery/lottery');	
	if(!$draw_prize) return false;
	$option = array(
		'where' => array(
			'aid' => $active_id,
			'uid' => $uid,
			'pid' =>$draw_prize['id'],
		),
		'table' => 'valentine_draw_award',
		'field' => 'id',
	);
	$res = $model->findOne($option,'lottery/lottery');	
	if($res) return false; 
	$redis -> set("{$prefix}:{$active_id}:prize_num:".$draw_prize['id'], 0);
	$where = array(
			'id' =>$draw_prize['id'],
			'aid' => $active_id,
	);
	$data = array(
			'num' => array('exp','`num`-1'),
			'__user_table' => 'valentine_draw_prize',
	);
	$model -> update($where,$data,'lottery/lottery');	
	$award_data = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $username,
		'create_tm' => $time,
		'status' => 1,
		'pid' => $draw_prize['id'],
		'prizename' => $draw_prize['name'],
		'__user_table' => 'valentine_draw_award'
	);
	$model -> insert($award_data,'lottery/lottery');	
	//领取日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => '',
		"time" => $time,
		"award_level" => $draw_prize['level'],
		"user" => $username,
		'uid'=>$uid,
		'key' => 'lottery',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	//抽奖成功日志
	$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"			=>	$_SERVER['REMOTE_ADDR'],
		"sid"			=>	'',
		"time"			=>	$time,
		"award_level"	=>	$draw_prize['level'],
		"user"			=>	$username,
		'uid'			=>	$uid,
		"award_name"	=>	$draw_prize['name'],
		'key'			=>	'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	$arr = array(	
		'uid'	=>	$uid,
		'type'	=>	1,
		'pid'	=> 	$draw_prize['level'],
		'prizename'	=>	$draw_prize['name'],
		'gift_number'	=>	'',
		'time'	=>	date("Y-m-d",$time)
	);		
	$key = 	"{$prefix}:{$active_id}_draw_award:{$uid}";
	$redis -> lPush($key,json_encode($arr), 30*60);	
}

/**
 * 我的领取的礼券记录
 * @param int $uid
 * @param int $aid
 * @param int $prefix
 * @param str $table
 * @return array
 */
function get_user_kind_award_list($uid, $aid)
{
	global $model;
	global $redis;
	global $prefix;
	$award_list_key = "{$prefix}:{$aid}_draw_award:{$uid}";
	$expire			= 30*60;

	$kind_award_list = $redis -> getlist($award_list_key);
	if( empty($kind_award_list) ) {
		$option = array(
				'table' => 'valentine_draw_award AS A',
				'where' => array(
						'A.uid' => $uid,
						'A.aid' => $aid,
				),
				'join' => array(
						'valentine_draw_prize AS B' => array(
								'on' => array('A.pid','B.id'),
						)
				),
				'field' => 'A.id,A.aid,A.uid,A.username,A.pid,A.prizename,A.create_tm,B.level,B.type',
		);

		$kind_award = $model->findAll($option,'lottery/lottery');
		$kind_award_list = array();

		if( !empty($kind_award) ) {
			foreach ((array)$kind_award as $k => $v ) {
				if( $v['type'] == 5 ) {
					$prizeArr		=	explode(':', $v['prizename']);
					$prizename		=	$prizeArr[0];
					$gift_number	=	$prizeArr[1];
				}else {
					$prizename		=	 $v['prizename'];
					$gift_number	=	'';
				}
				$kind_award_list[$k]['uid']			=	$uid;
				$kind_award_list[$k]['type']		=	$v['type'];
				$kind_award_list[$k]['pid']			=	$v['pid'];
				$kind_award_list[$k]['prizename']	=	$prizename;
				$kind_award_list[$k]['gift_number']	=	$gift_number;
				$kind_award_list[$k]['time']		=	$v['create_tm'];
			}
			$redis -> setlist($award_list_key, $kind_award_list,$expire);
			return $kind_award_list;
		}else {
			return false;
		}
	}else {
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}
		return $kind_award_list;
	}
}

function get_now_time(){
	global $model;
	$option = array(
			'where' => array(
					'status'  => 1,
					'conf_id' => 280
			),
			'table' => 'pu_config',
			'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}