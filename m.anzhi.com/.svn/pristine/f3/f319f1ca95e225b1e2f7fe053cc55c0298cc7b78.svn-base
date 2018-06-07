<?php
/*
** 初始页
*/
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model	=	new GoModel();

// cdn资源地址
//$tplObj -> out['static_url']		=	$configs['static_url'];
//$tplObj -> out['new_static_url']	=	$configs['new_static_url'];
$new_static_url = $configs['static_url'];
// cdn资源地址
$tplObj -> out['static_url'] = $configs['new_static_url'];
$tplObj -> out['new_static_url'] = $configs['static_url'];

//获取host
$activity_host = $configs['activity_url'];
// 当前时间
$now = time();
$today = date('Y-m-d');

// 加载session，获得用户相关信息
session_begin();
if( $_SESSION['USER_IMSI'] && $_SESSION['USER_IMSI'] != '000000000000000' ) {
	$imsi = $_SESSION['USER_IMSI'];
}else {
	$imsi = '';
}

$prefix		=	"beauty_puzzle";
$aid = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($aid)){
	exit;
}
$tplObj->out['aid']		=	$aid;
$tplObj->out['sid']		=	$sid;
$tplObj->out['imsi']	=	$imsi;
//获取host
$tplObj->out['activity_host']	=	$configs['activity_url'];
$tplObj -> out['is_test']		=	$configs['is_test'];

// 活动id
// 根据活动id获得软件页面id（主要用在活动页面下载软件，判断下载的软件是否有效）
$activity_option = array(
    'where' => array(
        'id'		=>	$aid,
        'status'	=>	1
    ),
    'cache_time'	=>	600,
    'table'			=>	'sj_activity'
);
$result = $model -> findOne($activity_option);

$category_option = array(
	'where' => array(
		'active_id'	=>	$result['activity_page_id'],
		'status'	=>	1,
	),
	'cache_time'=>	600,
	'table'		=>	'sj_actives_category',
	'field'		=>	'id',
);
$category = $model -> findAll($category_option);
$category = array_column($category,'id');

// 活动日志
$activity_log_file = "activity_{$aid}.log";


$activity_start_time = $result['start_tm'];//活动开始时间
$activity_end_time = $result['end_tm'];//活动结束时间
$activity_end_url = $result['end_url'];//活动结束页

//活动结束后延三天
$cache_time = ($result['end_tm'] - $now) + 86400*3;

//美女拼图
$conf_arr_1_1 = array(
		'1'	=>	$new_static_url.'/beauty_puzzle/images/puzzle_1.jpg',
		'2'	=>	$new_static_url.'/beauty_puzzle/images/puzzle_2.jpg',
		'3'	=>	$new_static_url.'/beauty_puzzle/images/puzzle_3.jpg',
);
//美女视屏
$conf_arr_1_3 = array(
		'1'	=>	array(
					'url'		=>	$new_static_url.'/beauty_puzzle/video/22.mp4',
					'screen'	=>	1,//横屏
				),
		'2'	=>	array(
				'url'		=>	$new_static_url.'/beauty_puzzle/video/meinv2.mp4',
				'screen'	=>	1,//横屏
		),
		'3'	=>	array(
				'url'		=>	$new_static_url.'/beauty_puzzle/video/meinv3.mp4',
				'screen'	=>	1,//横屏
		),
);

//猎奇动图
$conf_arr_2_1 = array(
		'1'	=>	$new_static_url.'/beauty_puzzle/images/gif_2_1.gif',
		'2'	=>	$new_static_url.'/beauty_puzzle/images/gif_2_2.gif',
		'3'	=>	$new_static_url.'/beauty_puzzle/images/gif_2_3.gif',
);
//猎奇视屏
$conf_arr_2_3 = array(
		'1'	=>	array(
				'url'		=>	$new_static_url.'/beauty_puzzle/video/11.mp4',
				'screen'	=>	1,//横屏
		),
		'2'	=>	array(
				'url'		=>	$new_static_url.'/beauty_puzzle/video/lieqi2.mp4',
				'screen'	=>	1,//横屏
		),
		'3'	=>	array(
				'url'		=>	$new_static_url.'/beauty_puzzle/video/lieqi3.mp4',
				'screen'	=>	1,//横屏
		),
);

//设置
function set_conf($opt, $step)
{
	global $redis;
	global $aid;
	global $imsi;
	global $cache_time;
	global $conf_arr_1_1;
	global $conf_arr_1_3;
	global $conf_arr_2_1;
	global $conf_arr_2_3;
	$pics_arr = array();
	if( $opt == 1 ) {
		if( $step == 1 ) {
			$pics_arr = $conf_arr_1_1;
		}elseif( $step == 3 ) {
			$pics_arr = $conf_arr_1_3;
		}else {
			return false;
		}
	}elseif( $opt == 2 ) {
		if( $step == 1 ) {
			$pics_arr = $conf_arr_2_1;
		}elseif( $step == 3 ) {
			$pics_arr = $conf_arr_2_3;
		}else {
			return false;
		}
	}else {
		return false;
	}
	$cur_key	=	"beauty_puzzle:{$aid}:{$imsi}:current-conf-{$opt}-{$step}";
	$key		=	"beauty_puzzle:{$aid}:{$imsi}:random-conf-{$opt}-{$step}";
	$pics = $redis->getlist($key);
	if( empty($pics) ) {
		$pic_k = array_rand($pics_arr, 1);
		//记录随机图片
		$redis -> lPush($key, $pic_k, $cache_time);
		//当前图片
		$re = $redis->set($cur_key, $pics_arr[$pic_k], $cache_time);
		return $re;
	}else {
		$new_pics_arr = $pics_arr;
		foreach($pics as $v) {
			unset($new_pics_arr[$v]);
		}
		if( empty($new_pics_arr) ) {
			$redis -> delete($key);
			$pic_k = array_rand($pics_arr, 1);
			$redis -> lPush($key, $pic_k, $cache_time);
			//当前图片
			$re = $redis->set($cur_key, $pics_arr[$pic_k], $cache_time);
			return $re;
		}else {
			$pic_k = array_rand($new_pics_arr, 1);
			$redis -> lPush($key, $pic_k, $cache_time);
			//当前图片
			$re = $redis->set($cur_key, $pics_arr[$pic_k], $cache_time);
			return $re;
		}
	}
}

//获取当前的拼图
function get_cur_conf($opt, $step)
{
	global $redis;
	global $aid;
	global $imsi;
	global $cache_time;
	global $pics_arr;
	$cur_key	=	"beauty_puzzle:{$aid}:{$imsi}:current-conf-{$opt}-{$step}";
	$val		=	$redis->get($cur_key);
	return $val;
}

//获取当前文案
function get_words($second)
{
	global $aid;
	global $sid;
	global $new_static_url;
	global $activity_log_file;
	$words = array(
			'1'	=>	array(
					'img'	=>	$new_static_url.'/beauty_puzzle/images/tips_03.png',
					'word'	=>	'王者级别！您已碾压100%的用户！', 
					),
			'2'	=>	array(
					'img'	=>	$new_static_url.'/beauty_puzzle/images/tips_04.png',
					'word'	=>	'好棒哦！您已击败80%的用户！', 
			),
			'3'	=>	array(
					'img'	=>	$new_static_url.'/beauty_puzzle/images/tips_05.png',
					'word'	=>	'再接再厉！亲的手速还需加强哦~', 
			),
			'4'	=>	array(
					'img'	=>	$new_static_url.'/beauty_puzzle/images/tips_06.png',
					'word'	=>	'等的花儿都谢了！你猜是倒数第几？', 
			),
			'5'	=>	array(
					'img'	=>	$new_static_url.'/beauty_puzzle/images/tips_07.png',
					'word'	=>	'这手速可够呛，搓搓手快去歇歇吧……', 
			),
	);
	if( $second < 10 ) {
		$key = 1;
	}elseif( 10 <= $second && $second <20 ) {
		$key = 2;
	}elseif( 20 <= $second && $second <30 ) {
		$key = 3;
	}elseif( 30 <= $second && $second <40 ) {
		$key = 4;
	}else {
		$key = 5;
	}
	$log_data = array(
			'imsi'			=>	$_SESSION['USER_IMSI'],
			'device_id'		=>	$_SESSION['DEVICEID'],
			'activity_id'	=>	$aid,
			'sid'			=>	$sid,
			'ip'			=>	$_SERVER['REMOTE_ADDR'],
			'time'			=>	time(),
			'users'			=>	'',
			'uid'			=>	'',
			'key'			=>	'puzzle_info',
			'second'		=>	$second,
			'level'			=>	$key,
	);
	permanentlog($activity_log_file, json_encode($log_data));
	return $words[$key];
}

//记录下载软件下载次数
function set_download_num($imsi, $opt, $step) {
	global $redis;
	global $aid;
	global $cache_time;
	$key	=	"beauty_puzzle:{$aid}:{$imsi}:download:$opt:$step";
	$re =  $redis->setx('incr', $key, 1);
	$redis->expire($key, $cache_time);
	return $re;
}
//删除下载的软件记录
function del_download_num($imsi, $opt, $step)
{
	global $redis;
	global $aid;
	global $cache_time;
	$key	=	"beauty_puzzle:{$aid}:{$imsi}:download:$opt:$step";
	$re =  $redis->delete($key);
	return $re;
}

//获取下载软件下载次数
function get_download_num($imsi, $opt, $step) {
	global $redis;
	global $aid;
	$key	=	"beauty_puzzle:{$aid}:{$imsi}:download:$opt:$step";
	return $redis->get($key);
}

function set_unlocck($imsi, $opt, $step) {
	global $redis;
	global $aid;
	$key	=	"beauty_puzzle:{$aid}:{$imsi}:is_unlock:$opt:$step";
	return $redis->set($key, 1, 86400);
}
//是否解锁
function get_unlock($imsi, $opt, $step)
{
	global $redis;
	global $aid;
	$key	=	"beauty_puzzle:{$aid}:{$imsi}:is_unlock:$opt:$step";
	return $redis->get($key);
}
