<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"smashed_egg";
$active_id	=	$_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit	检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$model = new GoModel();
if($configs['is_test'] == 1 ) {
	$time  = get_now_time();
}else {
	$time  = time();
}
$today = date("Ymd",$time);
//获取host
$activity_host = $configs['activity_url'];

//检查活动是否结束
if($_GET['stop'] != 1 ) {
	$res = activity_is_stop($active_id);
	if(!$res) {
		$url = $activity_host."/lottery/{$prefix}/2017_03_index.php?stop=1&aid=".$active_id;
		header("Location: {$url}");
	}
}
$channel_id = $res['channel_id'] ? $res['channel_id'] : '';
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
	global $active_id;
	$res = get_azb($uid,$active_id);
	if($res['code'] != '200') {
		$ret_arr = array(
				'code'	=>	0,
				'msg'	=>	$res['msg'],
		);
		return $ret_arr;
	}else {
		$res_info = json_decode($res['data'], true);
		$ret_arr = array(
			'code'		=>	1,
			'msg'		=>	$res['msg'],
			'azmoney'		=>	isset($res_info['azmoney']) ? $res_info['azmoney'] : 0,
			'isHasPayPwd'	=>	isset($res_info['isHasPayPwd']) ? $res_info['isHasPayPwd'] : 0,
		);
		return $ret_arr;
	}		
}
//消费安智币
function azb_consume($uid,$pwd,$azbAmount){
	global $active_id;	
	global $sid;	
	global $time;	
	global $model;	
	global $channel_id;	
	//试图消费日志
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
	
	$res = consume_azb($active_id,$uid,$pwd,$azbAmount,'三月砸蛋活动');
	//消费结果日志
	$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"			=>	$_SERVER['REMOTE_ADDR'],
		"sid"			=>	$sid,
		"time"			=>	$time,
		"user"			=>	$_SESSION['USER_NAME'],
		'uid'			=>	$uid,
		'azbAmount' => $azbAmount,//安智币		
		'return_code'   => 	$res['code'],
		'return_msg'    => $res['msg'],
		'key'			=>	'azb_consume_return',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	if($res['code'] != '200') {
		$ret_arr = array(
				'code'	=>	0,
				'msg'	=>	$res['msg'],
		);
		return $ret_arr;
	}else {
        $new_data = array(
			'uid' => $uid,
			'aid' => $active_id,
			'money' => $azbAmount,
			'add_tm' => $time,
			'__user_table' => 'consume' //消费记录表
        );	
		if($channel_id){
			$channel_arr = explode(",",substr($channel_id,1,-1));
			if(in_array('3150',$channel_arr) || in_array('271',$channel_arr)){
				$new_data['is_test'] = 1;
			}
		}		
        $model->insert($new_data,'lottery/lottery');		
		//消费成功日志
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
		
		$res_info = json_decode($res['data'], true);
		$ret_arr = array(
			'code'		=>	1,
			'msg'		=>	$res['msg'],
		);
		return $ret_arr;
	}		
}
//抽奖
function lottery_do($uid,$from_type,$azb_mount){
	global $active_id;	
	global $prefix;	
	load_helper('task');
	$position = array(
		1 => "1,2,3,4,5,6,7,8",
		2 => "12,13,14,15,16,17,18",
		3 => "22,23,24,25,26,27,28",
	);
	$task_client = get_task_client();	
	$new_array=array(
		'uid'		=>	$uid,
		'aid'		=>	$active_id,
		'username'	=>	$_SESSION['USER_NAME'],
		'prefix'	=>	$prefix,
		'position'	=>	$position[$from_type],
		'lottery_num' => $from_type == 3 ? 10 : 1,
		'azbAmount' => $azb_mount,
		'activityName' => '2017三月砸蛋',
	);
	$the_award = $task_client->do('yuandan_lottery', json_encode($new_array));	
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
				'table' => 'valentine_draw_award AS A', //中奖表
				'where' => array(
						'A.uid' => $uid,
						'A.aid' => $aid,
				),
				'join' => array(
						'valentine_draw_prize AS B' => array( //奖品配置表
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
//奖品展示
function get_prize_list(){
	global $model;
	global $redis;		
	global $active_id;		
	global $prefix;		
	$key = "{$prefix}:{$active_id}:prize_list";
	$prize_arr = $redis->get($key);
	if(empty($prize_arr)){
		$option = array(
			'where' => array(
				'level'	=>	array(1,11,21),
				'aid'	=>	$active_id, 
			),
			'table' => 'valentine_draw_prize', //奖品配置表
			'field' => 'id,level,name,num,type',
			'order' => 'level asc'
		);
		$prize_arr= $model->findAll($option,'lottery/lottery');
		$redis->set($key,$prize_arr,1200);
	}
	$return_arr = array();
	foreach($prize_arr as $k => $v){
		$num = $redis->get("{$prefix}:{$active_id}:prize_num:".$v['id']);
		$return_arr[$v['level']] = $num ? $num :$v['num'];
	}
	return 	$return_arr;	
}

function prize_user_config(){
	global $redis;		
	global $active_id;		
	global $prefix;		
	$key = "{$prefix}:{$active_id}:prize_user_config";
	$prize_user_config = $redis->get($key);
	if(empty($prize_user_config)){
		$prize_user_config = array(
			'20151222164540x50cMae6Mx' => array(
				'start_tm' => strtotime("2017-03-24 03:08:00"),
				'end_tm' => strtotime("2017-03-24 12:45:00"),
				'username' => 'az84607163',
				'is_award' => 0,
				'level' => 11,
			),
			'20131015143305u7C9xiLe1L' => array(
				'start_tm' => strtotime("2017-03-24 12:49:00"),
				'end_tm' => strtotime("2017-03-24 23:00:00"),
				'username' => 'aztt13716490',
				'is_award' => 0,
				'level' => 11,			
			),
			'201411121551270uP6zqbTRP' => array(
				'start_tm' => strtotime("2017-03-24 23:05:00"),
				'end_tm' => strtotime("2017-03-25 02:25:00"),
				'username' => 'azjxyz110',
				'is_award' => 0,
				'level' => 11,				
			),
			'20161027154025252EajojB9' => array(
				'start_tm' => strtotime("2017-03-25 02:33:00"),
				'end_tm' => strtotime("2017-03-25 12:00:00"),
				'username' => 'az132312049',
				'is_award' => 0,
				'level' => 11,			
			),
			'20160331103802I0wxhvMapI' => array(
				'start_tm' => strtotime("2017-03-25 12:03:00"),
				'end_tm' => strtotime("2017-03-25 16:10:00"),
				'username' => 'az100681553',
				'is_award' => 0,
				'level' => 11,				
			),
			'20160623205302pj8SFz1e8g' => array(
				'start_tm' => strtotime("2017-03-25 16:14:00"),
				'end_tm' => strtotime("2017-03-26 20:00:00"),
				'username' => 'az114224977',
				'is_award' => 0,
				'level' => 11,				
			),
			'201504281832037Dr9487aSz' => array(
				'start_tm' => strtotime("2017-03-26 20:01:00"),
				'end_tm' => strtotime("2017-03-26 16:10:00"),
				'username' => 'az53484482',
				'is_award' => 0,
				'level' => 11,				
			),
			'20141106140906i2ouEtLdou' => array(
				'start_tm' => strtotime("2017-03-26 16:20:00"),
				'end_tm' => strtotime("2017-03-26 23:50:00"),
				'username' => 'az30282450',
				'is_award' => 0,
				'level' => 11,				
			),
			'20161027155333n99rzw5GHs' => array(
				'start_tm' => strtotime("2017-03-24 22:36:00"),
				'end_tm' => strtotime("2017-03-25 11:09:00"),
				'username' => 'BO7452188',
				'is_award' => 0,
				'level' => 21,				
			),		
			'20161027155710A2MHey2QGu' => array(
				'start_tm' => strtotime("2017-03-25 11:10:00"),
				'end_tm' => strtotime("2017-03-26 09:10:00"),
				'username' => 'liangli04',
				'is_award' => 0,
				'level' => 21,				
			),			
			'20161027155851M4b749c8tu' => array(
				'start_tm' => strtotime("2017-03-25 09:17:00"),
				'end_tm' => strtotime("2017-03-26 14:15:00"),
				'username' => 'ab7756ri',
				'is_award' => 0,
				'level' => 21,				
			),			
			'20161027154825739rz2K1iP' => array(
				'start_tm' => strtotime("2017-03-26 14:20:00"),
				'end_tm' => strtotime("2017-03-26 23:55:00"),
				'username' => 'votreclemence',
				'is_award' => 0,
				'level' => 21,				
			),		
		);
		$redis->set($key,$prize_user_config,10*86400);
	}
	return $prize_user_config;
}
function add_test_user_award(){
	global $time;	
	$prize_user_config = prize_user_config();
	foreach($prize_user_config as $key => $val){
		if($val['is_award'] == 1) continue;
		if($time>=$val['start_tm'] && $time<=$val['end_tm']){
			add_test_award($key,$val['username'],$val['level']);
		}
	}
}
function add_test_award($uid,$username,$level){
	global $model;
	global $active_id;
	global $time;
	global $redis;
	global $prefix;	
	$option = array(
		'where' => array(
			'aid' => $active_id,
			'probability' => 0,
			'level' => $level
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
	$redis -> set("{$prefix}:{$active_id}:prize_num:".$draw_prize['id'], $draw_prize['num']);
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
	//抽奖日志
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
		'type'=>1,//1实物2礼包3谢谢参与4礼券5礼包（直接发放）			
		"award_level"	=>	$draw_prize['level'],
		"user"			=>	$username,
		'uid'			=>	$uid,
		"award_name"	=>	$draw_prize['name'],
		'from_type' => $draw_prize['level'] == 11 ? 2 : 3,//1免费砸蛋2一次砸蛋3十次砸蛋		
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
	
	$key = "{$prefix}:{$active_id}:prize_user_config";
	$prize_user_config = $redis->get($key);	
	$prize_user_config[$uid]['is_award'] = 1;
	$redis->set($key,$prize_user_config,10*86400);
}
function save_used_money($uid,$azb_mount){
	global $model;
	global $active_id;
	global $time;
	$where = array(
		'uid' => $uid,
		'aid' => $active_id 
	);	
	$data_update = array(
		'used_money' => array('exp',"`used_money`+{$azb_mount}"),
		'draw_data_num' => array('exp',"`draw_data_num`+1"),
		'update_tm' => $time,
		'__user_table' => 'valentine_draw_userinfo'
	);
	$model -> update($where,$data_update,'lottery/lottery');				
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
	//return strtotime('2017-01-01 02:33:00');
}