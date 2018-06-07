<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');

$config = load_config('lottery_cache/redis', "lottery");
if($config){
	$redis = new GoRedisCacheAdapter($config);
}else{
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

$prefix	= "double12_2017";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];

if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);

/*echo '<pre>';
$r_keys = $redis->getx('keys', "{$prefix}:*");
//print_r($r_keys);
foreach($r_keys as $key){
	$redis->delete($key);
}
echo '</pre>';*/

$time = time();
$today = date("Ymd", $time);
//获取host
$activity_host = $configs['activity_url'];

$stop = 0; //活动是否结束, 0:未结束, 1:结束
$res = activity_is_stop($active_id);
if(!$res){
	$stop = 1;
}

$channel_id = $res['channel_id'] ? $res['channel_id'] : '';

$tplObj -> out['sid']					=	$sid;
$tplObj -> out['aid']					=	$active_id;
$tplObj -> out['prefix']				=	$prefix;
$tplObj -> out['is_share']				=	$_GET['is_share'];
$tplObj -> out['is_test']				=	$configs['is_test'];
$tplObj -> out['static_url']			=	$configs['static_url'];
$tplObj -> out['new_static_url']		=	$configs['new_static_url'];
$tplObj -> out['activity_url']			=	$configs['activity_url'];
$tplObj -> out['activity_share_url']	=	$configs['activity_share_url'];
$tplObj -> out['version_code']			=	$_SESSION['VERSION_CODE'];

//获取用户安智币
function get_azmoney($uid){
	global $active_id;
	$res = get_azb($uid, $active_id);
	if($res['code'] != '200'){
		$ret_arr = array(
			'code'	=>	0,
			'msg'	=>	$res['msg'],
		);
		return $ret_arr;
	}else{
		$res_info = json_decode($res['data'], true);
		$ret_arr = array(
			'code'			=>	1,
			'msg'			=>	$res['msg'],
			'azmoney'		=>	isset($res_info['azmoney']) ? $res_info['azmoney'] : 0,
			'isHasPayPwd'	=>	isset($res_info['isHasPayPwd']) ? $res_info['isHasPayPwd'] : 0,
		);
		return $ret_arr;
	}
}

//支付安智币
function azb_consume($uid, $pwd, $azbAmount){
	global $active_id;
	global $sid;
	global $time;
	global $model;
	global $channel_id;
	//试图支付日志
	$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"			=>	$_SERVER['REMOTE_ADDR'],
		"sid"			=>	$sid,
		"time"			=>	$time,
		"user" 			=>	$_SESSION['USER_NAME'],
		'azbAmount' 	=>	$azbAmount,
		'uid'			=>	$uid,
		'key' 			=> 	'azb_consume',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

	$res = consume_azb($active_id, $uid, $pwd, $azbAmount, '双12开宝箱活动');
	//支付结果日志
	$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"			=>	$_SERVER['REMOTE_ADDR'],
		"sid"			=>	$sid,
		"time"			=>	$time,
		"user"			=>	$_SESSION['USER_NAME'],
		'uid'			=>	$uid,
		'azbAmount'		=>	$azbAmount,
		'return_code'   => 	$res['code'],
		'return_msg'    => 	$res['msg'],
		'key'			=>	'azb_consume_return',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	if($res['code'] != '200'){
		//支付失败日志
		$log_data = array(
			"imsi"			=>	$_SESSION['USER_IMSI'],
			"device_id"		=>	$_SESSION['DEVICEID'],
			"activity_id"	=>	$active_id,
			"ip"			=>	$_SERVER['REMOTE_ADDR'],
			"sid"			=>	$sid,
			"time"			=>	$time,
			"user"			=>	$_SESSION['USER_NAME'],
			'azbAmount' 	=> 	$azbAmount,
			'uid'			=>	$uid,
			'key'			=>	'azb_consume_failed',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		$ret_arr = array(
			'code'	=>	0,
			'msg'	=>	$res['msg'],
		);
		return $ret_arr;
	}else{
        $new_data = array(
			'uid' 			=> 	$uid,
			'aid' 			=> 	$active_id,
			'money' 		=> 	$azbAmount,
			'add_tm' 		=> 	$time,
			'__user_table' 	=> 	'consume' //消费记录表
        );
		if($channel_id){
			$channel_arr = explode(",", substr($channel_id, 1, -1));
			if(in_array('3150',$channel_arr) || in_array('271',$channel_arr)){
				$new_data['is_test'] = 1;
			}
		}
        $model->insert($new_data, 'lottery/lottery');
		//支付成功日志
		$log_data = array(
			"imsi"			=>	$_SESSION['USER_IMSI'],
			"device_id"		=>	$_SESSION['DEVICEID'],
			"activity_id"	=>	$active_id,
			"ip"			=>	$_SERVER['REMOTE_ADDR'],
			"sid"			=>	$sid,
			"time"			=>	$time,
			"user"			=>	$_SESSION['USER_NAME'],
			'azbAmount' 	=> 	$azbAmount,
			'uid'			=>	$uid,
			'key'			=>	'azb_consume_success',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

		$res_info = json_decode($res['data'], true);
		$ret_arr = array(
			'code'	=>	1,
			'msg'	=>	$res['msg'],
		);
		return $ret_arr;
	}
}

//抽奖
function lottery_do($uid, $from_type, $azb_mount){
	global $active_id;
	global $prefix;
	load_helper('task');
	$position = array(
		1 => "1,2",
		2 => "11,12,13,14",
		3 => "21,22,23,24",
		4 => "31,32,33,34",
	);
	$task_client = get_task_client();
	$new_array = array(
		'uid'			=>	$uid,
		'aid'			=>	$active_id,
		'username'		=>	$_SESSION['USER_NAME'],
		'prefix'		=>	$prefix,
		'position'		=>	$position[$from_type],
		'lottery_num' 	=> 	1,
		'azbAmount' 	=> 	$azb_mount,
		'activityName' 	=> 	'双12开宝箱',
	);
	$the_award = $task_client->do('yuandan_lottery', json_encode($new_array));
	if($the_award == '-1'){
		$ret_arr  = array(
			'code'	=> 	0,
			'msg'	=> 	'未中奖'
		);
		return $ret_arr;
	}else{
		return json_decode($the_award, true);
	}
}

//跑马灯
function get_award_record($aid){
	global $prefix;
	global $redis;
	global $model;
	$award_list = $redis -> getlist("{$prefix}:{$aid}_draw_award");
	if(!$award_list){
		$limit = 20;
		$where = array(
			'status' => 1,
			'pid' => array('exp',"not in(450,451,452,463)"),
			'aid' => $aid,
		);
		$option = array(
			'where' => $where,
			'order' => 'id desc',
			'limit' => $limit,	
			'table' => 'valentine_draw_award',
			'field' => 'username,prizename',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		$award_list = array();
		foreach((array)$kind_award as $k => $v){
			$award_list[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
			$award_list[$k]['prizename'] = $v['prizename'];
		}
		unset($kind_award);
		$redis -> setlist("{$prefix}:{$aid}_draw_award", $award_list, 15*60);
	}else{
		foreach($award_list as $k => $v){
			$award_list[$k] = json_decode($v, true);
		}	
	}	
	return $award_list;
}

//我的中奖纪录
function get_user_kind_award_list($uid, $aid){
	global $model;
	global $redis;
	global $prefix;
	$award_list_key	= "{$prefix}:{$aid}_draw_award:{$uid}";
	$expire	= 30*60;

	$kind_award_list = $redis->getlist($award_list_key);
	if(empty($kind_award_list)){
		$option = array(
			'table' => 'valentine_draw_award AS A', //中奖表
			'where' => array(
				'A.uid'	=>	$uid,
				'A.aid' =>	$aid,
			),
			'order' => 'A.id desc',
			'join' => array(
				'valentine_draw_prize AS B' => array( //奖品配置表
					'on' => array('A.pid', 'B.id'),
				)
			),
			'field' => 'A.id,A.aid,A.uid,A.username,A.pid,A.prizename,A.create_tm,B.level,B.type',
		);

		$kind_award = $model->findAll($option, 'lottery/lottery');
		$kind_award_list = array();

		if(!empty($kind_award)){
			foreach((array)$kind_award as $k => $v){
				$kind_award_list[$k]['uid']			=	$uid;
				$kind_award_list[$k]['type']		=	$v['type'];
				$kind_award_list[$k]['pid']			=	$v['pid'];
				$kind_award_list[$k]['prizename']	=	$v['prizename'];
				$kind_award_list[$k]['time']		=	$v['create_tm'];
			}
			$redis->setlist($award_list_key, $kind_award_list, $expire);
			return $kind_award_list;
		}else{
			return false;
		}
	}else{
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v, true);
		}
		return $kind_award_list;
	}
}

function save_used_money($uid, $azb_mount){
	global $model;
	global $active_id;
	global $time;
	$where = array(
		'uid' => $uid,
		'aid' => $active_id,
	);
	$data_update = array(
		'used_money' => array('exp', "`used_money`+{$azb_mount}"),
		'draw_data_num' => array('exp', "`draw_data_num`+1"),
		'update_tm' => $time,
		'__user_table' => 'valentine_draw_userinfo'
	);
	$model->update($where, $data_update, 'lottery/lottery');
}