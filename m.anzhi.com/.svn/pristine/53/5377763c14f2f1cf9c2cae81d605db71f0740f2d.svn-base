<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"september_sign";
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];

$model = new GoModel();
$time  = time();

if($_SERVER['SERVER_ADDR'] == '124.243.198.97' ){
	$activity_host = 'http://m.test.anzhi.com';
}else{
	$activity_host = 'http://promotion.anzhi.com';
}
if($_GET['stop'] != 1 ){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id;
		header("Location: {$url}");
	}
}


//获取活动配置
function get_config($aid, $uid){
	global $model;
	global $redis;	
	global $prefix;
	global $time;
	
	$tm_config = $redis->get("september:{$aid}_tm_config:".$uid);	
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
			date("Y-m-d",$start+86400*7) =>array(
				'num' => 8,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*7)),
			),
			date("Y-m-d",$start+86400*8) =>array(
				'num' => 9,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*8)),
			),
			date("Y-m-d",$start+86400*9) =>array(
				'num' => 10,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*9)),
			),
			date("Y-m-d",$start+86400*10) =>array(
				'num' => 11,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*10)),
			),
			date("Y-m-d",$start+86400*11) =>array(
				'num' => 12,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*11)),
			),
			date("Y-m-d",$start+86400*12) =>array(
				'num' => 13,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*12)),
			),
			date("Y-m-d",$start+86400*13) =>array(
				'num' => 14,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*13)),
			),
			date("Y-m-d",$start+86400*14) =>array(
				'num' => 15,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*14)),
			),
			date("Y-m-d",$start+86400*15) =>array(
				'num' => 16,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*15)),
			),
			date("Y-m-d",$start+86400*16) =>array(
				'num' => 17,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*16)),
			),
			date("Y-m-d",$start+86400*17) =>array(
				'num' => 18,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*17)),
			),
			date("Y-m-d",$start+86400*18) =>array(
				'num' => 19,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*18)),
			),
			date("Y-m-d",$start+86400*19) =>array(
				'num' => 20,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*19)),
			),
			date("Y-m-d",$start+86400*20) =>array(
				'num' => 21,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*20)),
			),
			date("Y-m-d",$start+86400*21) =>array(
				'num' => 22,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*21)),
			),
			date("Y-m-d",$start+86400*22) =>array(
				'num' => 23,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*22)),
			),
			date("Y-m-d",$start+86400*23) =>array(
				'num' => 24,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*23)),
			),
			date("Y-m-d",$start+86400*24) =>array(
				'num' => 25,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*24)),
			),
			date("Y-m-d",$start+86400*25) =>array(
				'num' => 26,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*25)),
			),
			date("Y-m-d",$start+86400*26) =>array(
				'num' => 27,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*26)),
			),
			date("Y-m-d",$start+86400*27) =>array(
				'num' => 28,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*27)),
			),
			date("Y-m-d",$start+86400*28) =>array(
				'num' => 29,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*28)),
			),
			date("Y-m-d",$start+86400*29) =>array(
				'num' => 30,
				'status' => 0,
				'time' => strtotime(date("Y-m-d",$start+86400*29)),
			)
		);
		
		//缓存挂掉对检查当天是否签到
		if( $uid ) {
			$sign_list = get_sign_list($uid);
			if( !empty($sign_list) ) {
				$key = date("Y-m-d", $time);
				if ( isset($sign_list[$key]) ) {
					$tm_config[$key]['status'] = 1;
				}
			}
		}
		$redis->set("{$prefix}:{$aid}_tm_config:".$uid,$tm_config,30*86400);
	}
	return $tm_config;			
}

/**
 * 获取某个用户的签到次数
 * @param int $uid 用户uid
 */
function get_sign_num($uid)
{
	global $model;
	global $redis;
	global $active_id;
	global $prefix;
	$sign_num_key = "{$prefix}:{$active_id}:my_sign_num:".$uid;
	$expire		  = 10*86400;
	
	$sign_num = $redis->get($sign_num_key);
	if( empty($sign_num) ) {
		$option = array(
			'where' => array(
				'uid' => $uid
			),
			'group'=> "FROM_UNIXTIME(create_tm, '%Y-%m-%d')",
			'table' => 'september_sign',
			'field' => '*'
		);
		$data = $model->findAll($option,'lottery/lottery');
		$sign_num = !empty($data) ? count($data) : 0;
		$redis->set($sign_num_key, $sign_num, $expire);
		return $sign_num;
	}else {
		return $sign_num;
	}
}

/**
 * 获取某个用户的签到记录
 * @param int $uid 用户uid
 */
function get_sign_list($uid)
{
	global $model;
	global $redis;
	global $active_id;
	global $prefix;
	$sign_list_key = "{$prefix}:{$active_id}:my_sign_List:".$uid;
	$expire		   = 10*86400;
	
	$sign_list = $redis->get($sign_list_key);
	if( empty($sign_list) ) {
		$option = array(
				'where' => array(
							'uid' => $uid
							),
				'table' => 'september_sign',
				'field' => '*'
		);
		$sign_list = $model->findAll($option,'lottery/lottery');
		if( !empty($sign_list) ) {
			$data = array();
			foreach ((array)$sign_list as $key => $val) {
				$key	= date("Y-m-d", $val['create_tm']);
				$data[$key] = strtotime(date("Y-m-d", $val['create_tm']));
			}
			$sign_list = $data;
		}
		$redis->set($sign_list_key, $sign_list, $expire);
		return $sign_list;
	}else {
		return $sign_list;
	}
}

/**
 * 增加签到记录
 * @param int $uid
 */
function add_sign_info($data)
{
	global $model;
	global $redis;
	global $active_id;
	global $prefix;
	global $time;
	$new_data = array(
			'uid'			=>	$data['uid'],
			'username'		=>	$_SESSION['USER_NAME'],
			'aid'			=>	$data['aid'],
			'create_tm'		=>	$time,
			'__user_table'	=>	'september_sign'
	);
	$result =  $model->insert($new_data,'lottery/lottery');
	
	if($result){
		$sign_num = $redis->setx('incr',"{$prefix}:{$active_id}:my_sign_num:".$data['uid'],+1);
		$redis->delete("{$prefix}:{$active_id}:my_sign_List:".$data['uid']);
		//sign_num 签到总次数
		switch ($sign_num) {
			case 3:
				$postion = 3;
				break;
			case 7:
				$postion = 7;
				break;
			case 15:
				$postion = 15;
				break;
			case 30:
				$postion = 30;
				break;
			default: 
				$postion = 0;
				break;
		}
		
		if( $postion ) {
			$keyPrefix = "{$prefix}:{$active_id}:user_sign_award_expire:{$data['uid']}:";
			$cacheKey = $keyPrefix.$postion;
			$expire   = strtotime(date("Y-m-d", $time));
			$redis -> set($cacheKey, $expire, 30*86400);
		}
		
	}
	return $result;
}

/**
 * 我的领取的礼券记录
 * @param int $uid
 * @param int $aid
 * @param int $prefix
 * @param str $table
 * @return array
 */
function get_user_kind_award_list($uid, $aid, $prefix, $table)
{
	global $model;
	global $redis;
	
	$award_list_key = "{$prefix}:{$aid}_user_draw_award:{$uid}";
	
	$kind_award_list = $redis -> get($award_list_key);
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
			'field' => 'A.id,A.aid,A.uid,A.username,A.pid,A.prizename,A.create_tm,B.level',
		);
		
		$kind_award = $model->findAll($option,'lottery/lottery');
		$kind_award_list = array();
		
		if( !empty($kind_award) ) {
			foreach ((array)$kind_award as $k => $v ) {
				$kind_award_list[$v['level']] = $v;
			}	
			$redis -> set($award_list_key, $kind_award_list, 30*60);
			return $kind_award_list;
		}else {
			return false;
		}
	}else {
		return $kind_award_list;
	}
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

/**
 * 获取礼券是否失效
 * @param str $uid
 * @param array $rule 满足礼券的几个天数
 * @return array|false
 */
function get_award_expire($uid, $rule) {
	global $model;
	global $redis;
	global $active_id;
	global $prefix;
	global $time;
	
	$result    = array();
	$keyPrefix = "{$prefix}:{$active_id}:user_sign_award_expire:{$uid}:";
	$sign_list = get_sign_list($uid);
	foreach ( (array)$rule as $val ) {
		$cacheKey = $keyPrefix.$val;
		$expire = $redis -> get($cacheKey);
		if( !empty($expire) ) {
			if( ($time-$expire) > (3*86400) ){
				//礼券失效
				$result[$val] = 1;
			} else {
				//礼券未失效
				$result[$val] = 0;
			}
		}else {
			if( !empty($sign_list) ) {
				$m = 1;
				foreach ($sign_list as $kk => $vv) {
					if( $m == $val ) {
						$value = strtotime(date("Y-m-d", $vv));
						$redis -> set($cacheKey, $value, 30*86400);
						if( ($time-$value) > (3*86400) ){
							//礼券失效
							$result[$val] = 1;
						} else {
							//礼券未失效
							$result[$val] = 0;
						}
					}
					$m++;
				}
			}else {
				return false;
			}
			
		}
	}
	
	return $result;
}

