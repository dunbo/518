<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis	=	new GoRedisCacheAdapter($config);
} else {
	$redis	=	GoCache::getCacheAdapter('redis');
}
$prefix		=	"red_ffl";
$active_id	=	$_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid		=	$_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if( !ctype_digit($active_id) ) {
	exit;
}

//防刷[进活动页面调起]
get_brush_bysn();

$model	=	new GoModel();

$time	=	time();

//获取host
$activity_host = $configs['activity_url'];

$activity = get_activity();

if( empty($activity) ) {
	echo '未配置活动';die;
}elseif( $activity['end_tm'] <= $time ) {
	//已结束
	$url = $activity['end_url'].'&aid='.$active_id.'&is_share='.$_GET['is_share'];
	header("Location: {$url}");
}elseif( $activity['start_tm'] > $time ) {
	//未开始
	$url = $activity['pre_url'].'&aid='.$active_id.'&is_share='.$_GET['is_share'];
	header("Location: {$url}");
}elseif( !$activity['red_init_num'] ) {
	echo '活动配置有误！请设置局数。';die;
}

//活动结束时间
$activity_end_tm = $activity['end_tm'] - $time;

//获取活动配置信息
function get_activity()
{
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	global $time;
	
	$activity_key	=	"{$prefix}:{$active_id}_sj_activity";
	$activity		=	$redis->get( $activity_key );
	$expire			=	3*86400;//三天
	if( empty($activity) ) {
		$option = array(
			'where'	=>	array('id' => $active_id,'status' => 1, 'activity_type'=>7),
			'table'	=>	'sj_activity',
			'field'	=>	'id,name,activity_type,imgurl,start_tm,end_tm,red_init_num,activity_page_id,pre_url,end_url,red_at_desc',
		);
		$activity = $model->findOne($option);
		if( !empty($activity) ) {
			$redis -> set($activity_key, $activity, $expire);
			return $activity;
		}else {
			return false;
		}
	}
	return $activity;
}

//奖品位置
function get_prize()
{
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	global $time;
	global $activity_end_tm;
	
	$prize_key	=	"{$prefix}:{$active_id}_sign_prize";
	$prize		=	$redis->get( $prize_key );
	if( empty($prize) ) {
		$option = array(
			'where' => array(
				'aid'		=>	$active_id,
				'status'	=>	1,
			),
			'field' => 'name,id,level,type',
			'table' => 'sign_prize'
		);
		$prize_result = $model -> findAll($option,'lottery/lottery');
		foreach($prize_result as $key => $val){
			$prize[$val['level']] = $val['type'];
		}
		if( !empty($prize) ) {
			$expire	=	$activity_end_tm + 86400;//活动结束后延的一天
			$redis -> set($prize_key, $prize, $expire);
			return $prize;
		}else {
			return false;
		}
	}
	return $prize;
}


//点击开始游戏
function start_game($aid, $uid, $param)
{
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	global $time;
	global $activity_end_tm;
	
	$user_num_key	=	"{$prefix}:{$active_id}_red_package_lottery_num_uid:".$uid;
	$expire			=	$activity_end_tm + 86400;//活动结束后延的一天
	$user_num		=	$redis->get( $user_num_key );
	if( empty($user_num) ) {
		$option = array(
				'where'	=>	array('aid' => $active_id,'uid' => $uid),
				'table'	=>	'red_package_lottery_num',
				'field'	=>	'*',
		);
		$user_num = $model->findOne($option,'lottery/lottery');
		if( empty($user_num) ) {
			//首次插入默认抽奖次数和局数
			$activity = get_activity();
			if( empty($activity) && $activity['red_init_num'] < 1 ) {
				return false;
			}
			$new_data = array(
					'imsi'			=>	$param['USER_IMSI'],
					'aid'			=>	$active_id,
					'uid'			=>	$uid,
					'username'		=>	$param['USER_NAME'],
					'update_tm'		=>	$time,
					'g_num'			=>	1,
					'lottery_num'	=>	0,
					'def_num'		=>	1,
					'end_num'		=>	0,
					'__user_table'	=>	'red_package_lottery_num'
			);
			$ret = $model->insert($new_data,'lottery/lottery');
			if( $ret ) {
				$redis -> set($user_num_key, $new_data, $expire);
				return $new_data;
			}else {
				return false;
			}
		}else {
			$redis -> set($user_num_key, $user_num, $expire);
			return $user_num;
		}
	}
	
	return $user_num;
}


//获取用户抽奖次数信息
function get_user_lottery_num( $uid )
{
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	global $time;
	global $activity_end_tm;

	$user_num_key	=	"{$prefix}:{$active_id}_red_package_lottery_num_uid:".$uid;
	$expire			=	$activity_end_tm + 86400;//活动结束后延的一天
	$user_num		=	$redis->get( $user_num_key );
	if( empty($user_num) ) {
		$option = array(
				'where'	=>	array('aid' => $active_id,'uid' => $uid),
				'table'	=>	'red_package_lottery_num',
				'field'	=>	'*',
		);
		$user_num = $model->findOne($option,'lottery/lottery');
		if( !empty($user_num) ) {
			$redis -> set($user_num_key, $user_num, $expire);
			return $user_num;
		}else {
			return false;
		}
	}
	return $user_num;
}

//用户抽奖统计缓存删除
function user_lottery_num_cache_delete($uid)
{
	global $redis;
	global $prefix;
	global $active_id;
	$key	=	"{$prefix}:{$active_id}_red_package_lottery_num_uid:".$uid;
	return $redis->delete($key);
}


//获取奖品信息
function get_prize_list()
{
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	global $activity_end_tm;
	global	$time;
	
	$key		=	"{$prefix}:{$active_id}_red_package_conf_level";
	$expire		=	$activity_end_tm + 86400;//活动结束后延的一天
	$prize_list	=	$redis->get( $key );
	if( empty($prize_list) ) {
		$option = array(
				'where'	=>	array('aid' => $active_id, 'status' =>	1),
				'table'	=>	'sign_prize',
				'field'	=>	'id,level,name,type,condition,oltime,redid',
		);
		$prize_list = $model->findOne($option,'lottery/lottery');
		if( !empty($prize_list) ) {
			$redis -> set($key, $prize_list, $expire);
			return $prize_list;
		}else {
			return false;
		}
	}
	return $prize_list;
}

//获取配置的红包数
function get_red_package_conf_num()
{
	global $model;
	global $redis;
	global $prefix;
	global $active_id;
	global $activity_end_tm;
	global	$time;
	
	$key		=	"{$prefix}:{$active_id}_red_package_conf_num";
	$expire		=	$activity_end_tm + 86400;//活动结束后延的一天
	$num		=	$redis->get( $key );
	if( empty($num) ) {
		$option = array(
				'where'	=>	array('aid' => $active_id, 'status' => 1, 'type' => 6),
				'table'	=>	'sign_prize',
				'field'	=>	'count(*) as count',
		);
		$num = $model->findOne($option,'lottery/lottery');
		$num = $num['count']?$num['count']:0;
		if( !empty($num) ) {
			$redis -> set($key, $num, $expire);
			return $num;
		}else {
			return 0;
		}
	}
	return $num;
}

//获取携带任务的软件包
function soft_task_list($aid, $uid)
{
	global $redis;
	global $prefix;
	$key	=	"{$prefix}:{$aid}_soft_task_uid:{$uid}";
	return $redis->getlist($key);
}
//获取携带已完成的任务的软件包
function soft_task_done_list($aid, $uid)
{
	global $redis;
	global $prefix;
	$key	=	"{$prefix}:{$aid}_soft_task_done_uid:{$uid}";
	return $redis->getlist($key);
}
//加入软件任务
function soft_task_add($aid, $uid, $pkg)
{
	global $redis;
	global $prefix;
	global $activity_end_tm;
	global $time;
	
	$key	=	"{$prefix}:{$aid}_soft_task_uid:{$uid}";
	$expire	=	$activity_end_tm + 86400;//活动结束后延的一天
	$redis->lrem($key, $pkg, 0);
	$res	=	$redis -> lPush($key, $pkg, $expire);
	if( $res ) {
		return json_encode(array('code'=>1,'msg'=>'成功'));
	}else {
		return json_encode(array('code'=>0,'msg'=>'失败'));
	}
}
//删除任务软件
function soft_task_delete($aid, $uid, $pkg)
{
	global $redis;
	global $prefix;
	
	$key	=	"{$prefix}:{$aid}_soft_task_uid:{$uid}";
	return $redis->lrem($key, $pkg, 0);
}

//已完成任务的软件
function soft_task_done_add($aid, $uid, $pkg)
{
	global $redis;
	global $prefix;
	global $activity_end_tm;
	global $time;
	
	$key	=	"{$prefix}:{$aid}_soft_task_done_uid:{$uid}";
	$expire	=	$activity_end_tm + 86400;//活动结束后延的一天
	$res	=	$redis -> lPush($key, $pkg, $expire);
	if( $res ) {
		return json_encode(array('code'=>1,'msg'=>'成功'));
	}else {
		return json_encode(array('code'=>0,'msg'=>'失败'));
	}
}

//再来一局
function reset_games($aid, $uid)
{
	global $model;
	global $redis;
	global $prefix;
	global $time;
	global $activity;

	//检查是否还有再来一局的机会
	$user_num = get_user_lottery_num($uid);
	//$activity = get_activity();
	if ( !$activity['red_init_num'] || ($activity['red_init_num']-$user_num['g_num']) <= 0 ) {
		return json_encode(array('code'=>0,'msg'=>'没有再来一局的机会了'));
	}
	$data = array(
		'g_num'			=>	array('exp',"`g_num`+1"),
		'lottery_num'	=>	0,
		'def_num'		=>	1,
		'end_num'		=>	0,
		'update_tm'		=>	$time,
		'__user_table'	=>	'red_package_lottery_num',
	);
	$where = array(
		'aid'	=>	$aid,
		'uid'	=>	$uid,
	);
	$res = $model->update($where, $data, 'lottery/lottery');
	if( $res ) {
		//删除记录上一局数的抽奖位置信息
		lottery_level_delete($aid, $uid, $user_num['g_num']);
		//删除用户统计缓存
		user_lottery_num_cache_delete($uid);
		return json_encode(array('code'=>1,'msg'=>'成功'));
	}else {
		return json_encode(array('code'=>0,'msg'=>'失败'));
	}
}


/**
 * 添加局数的位置
 * @param $aid
 * @param $uid
 * @param $g_num
 * @param $level
 * @param $is_award 是否中奖
 */
function lottery_level_add($aid, $uid, $g_num, $level, $is_award)
{
	global $redis;
	global $prefix;
	global $activity_end_tm;
	global $time;
	
	$key	=	"{$prefix}:{$aid}_ffl_jushu_level_status:{$g_num}_uid:{$uid}";
	$expire	=	$activity_end_tm + 86400;//活动结束后延的一天
	$data	=	json_encode(array('level'=>$level,'is_award'=>$is_award));
	return $redis -> lPush($key, $data, $expire);
}



//获取某局的抽奖位置
function lottery_level_list($aid, $uid, $g_num)
{
	global $redis;
	global $prefix;
	$key	=	"{$prefix}:{$aid}_ffl_jushu_level_status:{$g_num}_uid:{$uid}";
	return $redis->getlist($key);
}

//删除某局的抽奖位置
function lottery_level_delete($aid, $uid, $g_num)
{
	global $redis;
	global $prefix;
	$key	=	"{$prefix}:{$aid}_ffl_jushu_level_status:{$g_num}_uid:{$uid}";
	return $redis->delete($key);
}

//删除某局中的抽奖位置
function lottery_in_level_delete($aid, $uid, $g_num, $level, $is_award)
{
	global $redis;
	global $prefix;

	$key	=	"{$prefix}:{$aid}_ffl_jushu_level_status:{$g_num}_uid:{$uid}";
	$data	=	json_encode(array('level'=>$level,'is_award'=>$is_award));
	return $redis -> lrem($key, $data, 0);
}

//打开软件送抽奖机会
function open_soft($aid, $uid, $pkg)
{
	global $redis;
	global $model;
	global $perfix;
	global $time;
	
	$soft_list = soft_task_list($aid, $uid);
	if( !in_array($pkg, $soft_list) ) {
		return json_encode(array('code'=>0,'msg'=>'此软件不是在本活动下载，不予赠送抽奖机会！'));
	}
	//获取奖品记录表信息
	$user_num = get_user_lottery_num($uid);
// 	if( ($user_num['lottery_num'] + $user_num['def_num'] - $user_num['end_num']) > 0 ) {
// 		//删除任务软件,过滤已安装
// 		soft_task_delete($aid, $uid, $pkg);
// 		return json_encode(array('code'=>0,'msg'=>'本局已经送过抽奖机会了！'));
// 	}
	if($user_num['lottery_num']) {
		//删除任务软件,过滤已安装
		soft_task_delete($aid, $uid, $pkg);
		//加入已完成任务软件列表
		soft_task_done_add($aid, $uid, $pkg);
		return json_encode(array('code'=>0,'msg'=>'本局已经送过抽奖机会了！'));
	}
	
	$data = array(
			'lottery_num'	=>	1,
			'update_tm'		=>	$time,
			'__user_table'	=>	'red_package_lottery_num',
	);
	$where = array(
			'aid'	=>	$aid,
			'uid'	=>	$uid,
	);
	$res = $model->update($where, $data, 'lottery/lottery');
	if( $res ) {
		//删除任务软件
		soft_task_delete($aid, $uid, $pkg);
		//加入已完成任务软件列表
		soft_task_done_add($aid, $uid, $pkg);
		//删除用户统计缓存
		user_lottery_num_cache_delete($uid);
		return json_encode(array('code'=>1,'msg'=>'成功'));
	}else {
		return json_encode(array('code'=>0,'msg'=>'失败'));
	}
}

//翻牌抽奖
function lottery_do($aid, $uid, $level, $session)
{
	global $redis;
	global $model;
	global $perfix;
	global $time;
	global $active_id;
	
	$user_num = get_user_lottery_num($uid);
// 	if( ($user_num['lottery_num']+$user_num['def_num']) <= $user_num['end_num'] ) {
// 		return json_encode(array('code'=>0,'msg'=>'没有抽奖机会了！'));
// 	}
	//防刷处理
	$bursh_info	=	get_brush_all($active_id, 2);
	if( $bursh_info['code'] == 0 ) {
		$award = -1; //刷的直接未中奖
	}else {
		//work抽奖
		load_helper('task');
		$task_client = get_task_client();
		$need = array(
				'mac'		=>	$session['MAC'],
				'imei'		=>	$session['DEVICEID'],
				'imsi'		=>	$session['USER_IMSI'],
				'uid'		=>	$uid,
				'session'	=>	$session,
				'aid'		=>	$active_id,
		);
		$award = $task_client->do('red_package_worker',json_encode($need));
	}
	if( $award ) {
		$data = array(
				'end_num'		=>	array('exp',"`end_num`+1"),
				//'end_num'		=>	0,
				'update_tm'		=>	$time,
				'__user_table'	=>	'red_package_lottery_num',
		);
		$where = array(
				'aid'	=>	$aid,
				'uid'	=>	$uid,
		);
		$res = $model->update($where, $data, 'lottery/lottery');
		//加入到本局的抽奖位置
		if( $award == -1 ) {
			lottery_level_add($aid, $uid, $user_num['g_num'], $level, 2);
		}else {
			lottery_level_add($aid, $uid, $user_num['g_num'], $level, 1);
		}
		user_lottery_num_cache_delete($uid);
		return $award;
	}else {
		return false;
	}
}

//获取未领取的红包记录
function get_award($aid, $uid)
{
	global $model;
	global $redis;
	global $time;
	
	$hash_key = "red_package:{$aid}:uid:{$uid}:red_package_list";
	$award_list = $redis->gethash($hash_key);
	if( !empty($award_list) ) {
		foreach ( $award_list as $k => $v ) {
			//大于一个小时红包置为已过期
			if( $time - $v['time'] > 3600 ) {
				$data = array(
						'status'		=>	2,
						'__user_table'	=>	'red_package_award',
				);
				$where = array(
						'id'	=>	$k,
						'aid'	=>	$aid,
						'uid'	=>	$uid,
						
				);
				$res = $model->update($where, $data, 'lottery/lottery');
				if( $res ) {
					//删除key
					unset($award_list[$k]);
					$redis->hdel($hash_key, $k);
				}
			}
		}
		//根据key排序
		krsort($award_list);
	}
	return $award_list;
}


function open_red($aid, $uid, $inserId) {
	global $redis;
	
	$hash_key	=	"red_package:{$aid}:uid:{$uid}:red_package_list";
	$key		=	$inserId;
	$red_info	=	$redis->gethash($hash_key, $key);
	if( $red_info ) {
		$app_info = array();
		if( $red_info['package'] ) {
			$app_info = get_app_info($red_info['package']);
		}
		$activity = get_activity();
		$param = array(
				'activityName'	=>	$activity['name'],
				'type'			=>	0,
				'activityId'	=>	$aid,
				'activityType'	=>	7,
				'redPackId'		=>	$red_info['red_id'],
				'LRTS'			=>	1,
				'APP_INFO'		=>	$app_info?json_decode($app_info, true):"",
				'status'		=>	$red_info['package']?($app_info?$red_info['status']:3):0,
				'orderId'		=>	$inserId,
		);
		$param_json = json_encode($param);
		return $param_json;
	}else {
		return false;
	}
}


function get_now_time(){
	global $model;
	$option = array(
			'where' => array(
					'status'	=>	1,
					'conf_id'	=>	280
			),
			'table'	=>	'pu_config',
			'field'	=>	'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}

function values2keys($arr){
	$new = array();
	while ( list($k,$v) = each($arr) ){
		$v = json_decode($v, true);
		if ($v){
			$new[$v['level']] = $v['is_award'];
		}
	}
	return $new;
}

function get_cur_draw_num($arr)
{
	if(!empty($arr) ) {
		$count = 0;
		foreach ( $arr as $v ) {
			$v = json_decode($v, true);
			if($v['is_award'] == 1) {
				$count ++;
			}
		}
		return $count;
	}else {
		return 0;
	}
}

function db_and_cache_all_delete($uid)
{
	global $redis;
	global $model;
	global $perfix;
	global $active_id;
	//删除活动缓存
	$activity_key	=	"{$perfix}:{$active_id}_sj_activity";
	$redis->delete($activity_key);
	//删除任务软件缓存
	$task_key = "{$perfix}:{$active_id}_soft_task_uid:{$uid}";
	$redis->delete($task_key);
	
	user_lottery_num_cache_delete($uid);
	
	lottery_level_delete($active_id, $uid, 1);
	lottery_level_delete($active_id, $uid, 2);
	lottery_level_delete($active_id, $uid, 3);
	lottery_level_delete($active_id, $uid, 4);
	lottery_level_delete($active_id, $uid, 5);
	
	$where = array(
		'aid'	=>	$active_id,
		'uid'	=>	$uid,
		'__user_table'	=>	'red_package_lottery_num',
	);
	$res = $model->delete($where, 'lottery/lottery');
	if( $res ){
		echo '成功';die;
	}else {
		echo '失败';die;
	}
}
