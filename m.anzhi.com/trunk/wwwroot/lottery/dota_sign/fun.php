<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$prefix		=	"dota_sign";
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
			)
		);
		
		$redis->set("{$prefix}:{$aid}_tm_config:".$uid,$tm_config,8*86400);
	}
	return $tm_config;			
}


/**
 * 已签到数
 * @param jint $uid
 * @return number
 */
function get_sign_num($uid)
{
	global $redis;
	global $active_id;
	global $prefix;
	$sign_num_key = "{$prefix}:{$active_id}:user_sign_num:".$uid;
	$expire		  = 8*86400;
	
	$sign_num = $redis->get($sign_num_key);
	if( !isset($sign_num) ) {
		$sign_num = 0;
		$configInfo	=	get_config($active_id, $uid);
		foreach ($configInfo as $k => $v) {
			if($v['status'] == 1) {
				$sign_num ++;
			}
		}
		$redis->set($sign_num_key, $sign_num, $expire);
		return $sign_num;
	}else {
		return $sign_num;
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
function get_user_kind_award_list($uid, $aid, $prefix, $table)
{
	global $model;
	global $redis;
	
	$award_list_key = "{$prefix}:{$aid}_user_draw_award:{$uid}";
	$expire			= 8*86400;
	
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
			'field' => 'A.id,A.aid,A.uid,A.username,A.pid,A.prizename,A.create_tm,B.level,B.type',
		);
		
		$kind_award = $model->findAll($option,'lottery/lottery');
		$kind_award_list = array();
		
		if( !empty($kind_award) ) {
			foreach ((array)$kind_award as $k => $v ) {
				$kind_award_list[$v['level']] = $v;
			}	
			$redis -> set($award_list_key, $kind_award_list, $expire);
			return $kind_award_list;
		}else {
			return false;
		}
	}else {
		return $kind_award_list;
	}
}

function get_soft_info($pkg){
	global $model;
	$option = array(
			'where' => array(
					'status' => 1,
					'hide' => 1,
					'package' => $pkg
			),
			'table' => 'sj_soft',
			'field' => 'softid,softname,version_code',
			'order' => 'softid desc',
			'cache_time' => 86400
	);
	$list = $model->findOne($option);
	return $list;
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


