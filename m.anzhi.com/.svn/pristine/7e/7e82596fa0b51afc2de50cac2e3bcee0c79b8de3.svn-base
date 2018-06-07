<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();

// 常量
if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
	define('SHARE_PROMOTION_HOST', 'http://test.m.anzhi.com');
	define('SHARE_M_HOST', 'http://test.m.anzhi.com');
} else {
	define('SHARE_PROMOTION_HOST', 'http://promotion.anzhi.com');
	define('SHARE_M_HOST', 'http://m.anzhi.com');
	define('SHARE_FX_HOST', 'http://fx.anzhi.com');
}

$tplObj->out['SHARE_PROMOTION_HOST'] = SHARE_PROMOTION_HOST;
$tplObj->out['SHARE_M_HOST'] = SHARE_M_HOST;

// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];

// 当前时间
$now = time();
$today = date('Y-m-d');

// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

//////// test
/*
if ($sid == '123') {
	$imsi = '460077112634262';
	$_SESSION['VERSION_CODE'] = 6000;
}
*/
/////////////

$imsi_status = 0;
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
} else {
    $imsi_status = 1;
}
$tplObj->out['sid'] = $sid;
$tplObj->out['imsi_status'] = $imsi_status;

//$aid = 431;//测试环境aid
$aid = 437;//线上aid
$activity_log_file = "activity_{$aid}.log";// 活动日志
$promotion_share_url = SHARE_PROMOTION_HOST . '/lottery/guessappbattle/weixin_index.php';//活动分享页
$share_big_pic = $configs['new_static_url'] . '/activity/guessappbattle/images/share_big.jpg';//活动分享大图
$share_small_pic = $configs['new_static_url'] . '/activity/guessappbattle/images/share_small.jpg';//活动分享小图
$fix_package = array();
$tplObj->out['stable_soft_lists'] = json_encode($fix_package);//软件列表固定的四个软件

// 答题相关
$correctnumber_score_arr = array(0,15,36,36,73,73,73,90,100);//答题正确个数对应的分数
// 活动软件分类
/* // 测试环境软件分类
$fix_category_id = 250;
$score_arr = array('0' => 204, '15' => 205, '36' => 206, '73' => 207, '90' => 208, '100' => 249);//分数对应软件分类
$common_category_id = 257;
*/
// 线上环境软件分类
$fix_category_id = 169;
$score_arr = array('0' => 170, '15' => 171, '36' => 172, '73' => 173, '90' => 174, '100' => 175);//分数对应软件分类
$common_category_id = 176;
$tplObj->out['fix_category_id'] = $fix_category_id;
$tplObj->out['common_category_id'] = $common_category_id;

// 根据活动id获得软件页面id（主要用在活动页面下载软件，判断下载的软件是否有效）
$activity_option = array(
    'where' => array(
        'id' => $aid,
        'status' => 1
    ),
    'cache_time' => 600,
    'table' => 'sj_activity'
);
$result = $model -> findOne($activity_option);
$page_id = $result['activity_page_id'];//活动页面id，用来关联软件
$activity_url = $result['url'];//活动开始时间
$activity_start_time = $result['start_tm'];//活动开始时间
$activity_end_time = $result['end_tm'];//活动结束时间

// 根据aid去奖品表查
$option = array(
	'where' => array(
		'aid' => $aid,
		'status' => 1
	),
	'cache_time' => '600',
	'order' => 'level',
	'table' => 'brand_general_lottery_prize',
);
$ret = $model->findAll($option, 'lottery/lottery');
$award_config = array();
foreach ($ret as $row) {
	$level = $row['level'];
	$award_config[$level] = $row;
}

$tplObj->out['award_count'] = count($award_config);

// 活动id
$tplObj->out['aid'] = $aid;
$tplObj->out['promotion_share_url'] = $promotion_share_url;
$tplObj->out['share_big_pic'] = $share_big_pic;
$tplObj->out['share_small_pic'] = $share_small_pic;

// 中奖人名单
$option = array(
	'where' => array(
		'telephone' => array('exp', "!=''"),
		'aid' => $aid,
	),
	'order' => 'id desc',
	'cache_time' => '600',
	'limit' => '10',
	'table' => 'brand_general_lottery_award'
);
$people_award_list = $model->findAll($option, 'lottery/lottery');
foreach ($people_award_list as $key => $row) {
	// 电话号码加密
	$people_award_list[$key]['telephone'] = substr_replace($row['telephone'], '****', 3, 4);
	// 获得中奖内容
	$award_level = $row['level'];
	$people_award_list[$key]['award_name'] = $award_config[$award_level]['name'];
	// 中奖时间
	$people_award_list[$key]['date'] = date('Y-m-d', $row['time']);
}
$tplObj->out['people_award_list'] = $people_award_list;

// redis缓存key
$r_cache_time = '5184000';//redis缓存时间为两个月
$rkey_imsi_share = "guessappbattle:{$aid}:{$imsi}:share";//用户最新一次分享的日期
$rkey_imsi_lottery_num = "guessappbattle:{$aid}:{$imsi}:lottery_num";//用户可抽奖次数
$rkey_imsi_package_list = "guessappbattle:{$aid}:{$imsi}:package_list";//用户下载软件列表
$rkey_imsi_score = "guessappbattle:{$aid}:{$imsi}:score";//用户答题分数

if ($imsi_status) {
	// 初始化用户
	$lottery_num = init_user();
	$tplObj->out['lottery_num'] = $lottery_num;
}

function init_user() {
	return get_lottery_num();
}

function get_lottery_num() {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num, $aid;
	
	// 获得用户的可抽奖次数
	$lottery_num = $redis->get($rkey_imsi_lottery_num);
	
	if (empty($lottery_num) && $lottery_num !== 0) {
		// 可能没有此用户，或缓存已失效
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $aid,
			),
			'table' => 'brand_general_lottery_num',
		);
		$find = $model->findOne($option, 'lottery/lottery');
		if ($find) {
			$lottery_num = (int)$find['lottery_num'];
		} else {
			$lottery_num = 0;
			// insert
			$data = array(
				'imsi' => $imsi,
				'lottery_num' => $lottery_num,
				'aid' => $aid,
				'time' => time(),
				'__user_table' => 'brand_general_lottery_num',
			);
			$model->insert($data, 'lottery/lottery');
		}
		$redis->set($rkey_imsi_lottery_num, $lottery_num, $r_cache_time);
	}

	return $lottery_num;
}

function set_lottery_num($lottery_num) {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num, $aid;
	get_lottery_num();
	$redis->set($rkey_imsi_lottery_num, $lottery_num, $r_cache_time);
	$where = array(
		'imsi' => $imsi,
		'aid' => $aid,
	);
	$data = array(
		'lottery_num' => $lottery_num,
		'time' => time(),
		'__user_table' => 'brand_general_lottery_num',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}

function reduce_lottery_num_by_1() {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num, $aid;
	// 先尝试获得抽奖机会
	$now_num = get_lottery_num();
	$now_num = (int)$now_num;
	if ($now_num <= 0) {
		// 没有抽奖机会
		return false;
	}
	$now_num = $redis->setx('incr', $rkey_imsi_lottery_num, -1);
	if (!is_int($now_num)) {
		// 出错
		return false;
	}
	if ($now_num < 0) {
		// 没有抽奖机会，把缓存数量还原为0
		$now_num = $redis -> set($rkey_imsi_lottery_num, 0);
		return false;
	}
	// 写回库里
	$where = array(
		'imsi' => $imsi,
		'aid' => $aid,
	);
	$data = array(
		'lottery_num' => array('exp', '`lottery_num`-1'),
		'time' => time(),
		'__user_table' => 'brand_general_lottery_num',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}