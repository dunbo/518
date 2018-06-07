<?php

/*
** 初始页
*/
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();

// 常量
define('SHARE_PROMOTION_HOST', 'http://promotion.anzhi.com');
define('SHARE_M_HOST', 'http://m.anzhi.com');

//define('SHARE_PROMOTION_HOST', 'http://118.26.203.23');
//define('SHARE_M_HOST', 'http://118.26.203.23');

$tplObj->out['SHARE_PROMOTION_HOST'] = SHARE_PROMOTION_HOST;
$tplObj->out['SHARE_M_HOST'] = SHARE_M_HOST;

// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];

// sid
session_begin();
$sid = $_GET['sid'];

$tplObj->out['sid'] = $sid;

if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$imsi_status = 0;
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
} else {
    $imsi_status = 1;
}
$tplObj->out['imsi_status'] = $imsi_status;

// 活动id，奖品配置
$activity_option = array(
    'where' => array(
        'config_type' => 'TIMEMEMORY_LOTTERY_AWARD',
        'status' => 1
    ),
    'cache_time' => 86400,
    'table' => 'pu_config'
);
$result = $model -> findOne($activity_option);
$activity_config = json_decode($result['configcontent'],true);

$aid = $activity_config['activity_id'];//活动id
$page_id = $activity_config['page_id'];//活动页面id，用来关联软件
$award_config = $activity_config['award_config'];

// 活动id
$tplObj->out['aid'] = $aid;

// redis相关
$r_cache_time = '5184000';//redis缓存时间为两个月
// 缓存key
$rkey_imsi_open_info = "timememory:{$aid}:{$imsi}:open_info";//用户打开此活动天数信息，hash类型
$rkey_imsi_question_answer = "timememory:{$aid}:{$imsi}:question_answer";//用户回忆问题答案，hash类型
$rkey_imsi_share_info = "timememory:{$aid}:{$imsi}:share_info";//用户最后一次分享日期，字符串类型
$rkey_imsi_lottery_num = "timememory:{$aid}:{$imsi}:lottery_num";//用户每天分享次数
$rkey_imsi_package_list = "timememory:{$aid}:{$imsi}:package_list";

// 活动日志
$activity_log_file = "activity_{$aid}.log";

$today = date('Y-m-d');

// 中奖人名单
$option = array(
	'where' => array(
		'telephone' => array('exp', "!=''")
	),
	'order' => 'id desc',
	'cache_time' => '3600',
	'limit' => '10',
	'table' => 'timememory_lottery_award'
);
$people_award_list = $model->findAll($option, 'lottery/lottery');
foreach ($people_award_list as $key => $row) {
	// 电话号码加密
	$people_award_list[$key]['telephone'] = substr_replace($row['telephone'], '****', 3, 4);
	// 获得中奖内容
	$award_level = $row['award'];
	$people_award_list[$key]['award_name'] = $award_config[$award_level - 1][1];
}

$tplObj->out['people_award_list'] = $people_award_list;


function get_lottery_num() {
	global $imsi, $model, $redis, $rkey_imsi_lottery_num, $r_cache_time;
	
	$now = time();
	$now_num = $redis->get($rkey_imsi_lottery_num);
	if (empty($now_num) && $now_num !== 0) {
		// 尝试从表里读出来
		$option = array(
			'where' => array(
				'imsi' => $imsi
			),
			'table' => 'timememory_lottery_num',
		);
		$find = $model->findOne($option, 'lottery/lottery');
		if (empty($find)) {
			$now_num = 0;
			$ret = $model->query("insert into `timememory_lottery_num` (`imsi`, `lottery_num`, `time`) values ('{$imsi}', {$now_num}, {$now}) ON DUPLICATE KEY UPDATE lottery_num={$now_num};", 'lottery/lottery');
		} else {
			$now_num = $find['lottery_num'];
		}
		// 更新缓存
		$redis->set($rkey_imsi_lottery_num, $now_num, $r_cache_time);
	}
	return $now_num;
}

function set_lottery_num($now_num) {
	global $imsi, $model, $redis, $rkey_imsi_lottery_num, $r_cache_time;
	
	$now = time();
	//更新缓存
	$redis->set($rkey_imsi_lottery_num, $now_num, $r_cache_time);
	//更新数据库
	$where = array(
		'imsi' => $imsi,
	);
	$data = array(
		'lottery_num' => $now_num,
		'time' => $now,
		'__user_table' => 'timememory_lottery_num',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	if ($ret === false) {
		// insert
		$data = array(
			'imsi' => $imsi,
			'lottery_num' => $now_num,
			'time' => $now,
			'__user_table' => 'timememory_lottery_num',
		);
		$ret = $model->insert($data, 'lottery/lottery');
	}
	return $ret;
}

// 用户可抽奖次数-1，如果没有抽奖机会，直接返回false
function reduce_lottery_num_by_1() {
	global $imsi, $model, $redis, $rkey_imsi_lottery_num, $r_cache_time;
	
	// 先尝试获得抽奖机会
	$now_num = get_lottery_num();
	if ($now_num <= 0) {
		// 没有抽奖机会
		return false;
	}
	
	$now = time();
	// 可抽奖次数-1
	$now_num = $redis -> setx('incr',$rkey_imsi_lottery_num, -1);
	if ($now_num < 0) {
		// 没有抽奖机会，把缓存数量还原为0
		$now_num = $redis -> set($rkey_imsi_lottery_num, 0);
		return false;
	}
	// 写回库里
	$where = array(
		'imsi' => $imsi
	);
	$data = array(
		'lottery_num' => $now_num,
		'time' => $now,
		'__user_table' => 'timememory_lottery_num'
	);
	$model -> update($where,$data,'lottery/lottery');
	return true;
}