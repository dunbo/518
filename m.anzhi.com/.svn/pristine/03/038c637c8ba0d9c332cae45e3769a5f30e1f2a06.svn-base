<?php
/*
** 活动初始页
*/
// 要接收的参数 $_GET['is_share'],$_GET['sid'],$_GET['aid']
include_once(dirname(realpath(__FILE__)).'/../../init.php');

list($redis, $model) = load_config_redis();

// cdn资源地址
$tplObj->out['static_url'] = $configs['static_url'];
$tplObj->out['new_static_url'] = $configs['new_static_url'];
$tplObj->out['activity_share_url'] = $configs['activity_share_url'];
$tplObj->out['is_share'] = $_GET['is_share'];

// sn无效进黑名单函数
get_brush_bysn();

// 当前时间
$now = time();
$date = date('Ymd');
$date = (int)$date; //对应游戏次数表`date`字段

// 加载session，获取用户相关信息
session_begin();
$sid = $_GET['sid'];
$tplObj->out['sid'] = $sid;
if ($_SESSION['USER_IMSI']) {
    $imsi = $_SESSION['USER_IMSI'];
}
// $imsi = '405028011771946'; //测试用
$imsi_status = 0; //用户状态 0：异常 1：正常
if (!$imsi || $imsi == '000000000000000') {
	$imsi = '';
} else {
	$imsi_status = 1;
}

// redis相关
$r_cache_time = 5184000; //redis缓存时间为两个月
$rkey_imsi_game_num = "forfather_201706:{$aid}:{$imsi}:{$date}:game_num"; //用户已玩游戏次数
$rkey_imsi_lottery_num = "forfather_201706:{$aid}:{$imsi}:lottery_num"; //用户剩余抽奖次数
$rkey_imsi_package_list = "forfather_201706:{$aid}:{$imsi}:package_list"; //下载的软件

$aid = $_GET['aid'];
$tplObj->out['aid'] = $aid;
$tplObj->out['imgurl'] = getImageHost();

// 根据活动id，获取活动页面id，用来关联软件
$activity_option = array(
    'where' => array(
        'id' => $aid,
        'status' => 1
    ),
    'cache_time' => 600,
    'table' => 'sj_activity'
);
$result = $model->findOne($activity_option);
$page_id = $result['activity_page_id']; //活动页面id，在download_api.php中验证下载的包是否有效
$a_start_time = $result['start_tm']; //活动开始时间
$a_end_time = $result['end_tm']; //活动结束时间

// 活动日志
$activity_log_file = "activity_{$aid}.log";

// 中奖人名单
$award_level_name_arr = array('', '一等奖', '二等奖', '三等奖', '四等奖', '五等奖', '六等奖', '七等奖');
$option = array(
	'where' => array(
		'telephone' => array('exp', "!=''"),
		'status' => 1,
		'aid' => $aid,
	),
	'order' => 'id desc',
	'cache_time' => '600',
	'limit' => '10',
	'table' => 'imsi_lottery_award' //中奖信息表
);
$people_award_list = $model->findAll($option, 'lottery/lottery');
foreach ($people_award_list as $key => $row) {
	// 电话号码加密
	$people_award_list[$key]['telephone'] = substr_replace($row['telephone'], '****', 3, 4);
	// 获取中奖内容
	$award_level = $row['award'];
	$prize_name = get_prize_name($award_level);
	$people_award_list[$key]['award_name'] = $prize_name;
	// 中奖时间
	$people_award_list[$key]['date'] = date('Y-m-d', $row['time']);
}
$tplObj->out['people_award_list'] = $people_award_list; //中奖人名单

if ($imsi_status) {
	// 初始化用户游戏次数、抽奖次数
	$game_num = get_game_num();
	$tplObj->out['game_num'] = $game_num;
	$lottery_num = get_lottery_num();
	$tplObj->out['lottery_num'] = $lottery_num;
}

// 获取抽奖次数
function get_lottery_num() {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num, $aid;
	// 先从缓存获取剩余抽奖次数
	$lottery_num = $redis->get($rkey_imsi_lottery_num);
	if (empty($lottery_num) && $imsi) {
	// 可能没有此用户，或缓存已失效，从数据库获取信息
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $aid,
			),
			'table' => 'imsi_lottery_num' //抽奖机会表
		);
		$find = $model->findOne($option, 'lottery/lottery');
		if ($find) {
			$lottery_num = (int)$find['lottery_num'];
		} else {
			// 数据库没有记录，插入一条新记录
			$imsi_data = array(
				'aid' => $aid,
				'imsi' => $imsi,
				'lottery_num'=> 0,
				'time' => time(),
				'__user_table' => 'imsi_lottery_num' //抽奖机会表
			);
			$model->insert($imsi_data, 'lottery/lottery');
			$lottery_num = 0;
		}
		// 将剩余抽奖次数加入缓存
		$redis->set($rkey_imsi_lottery_num, $lottery_num, $r_cache_time);
	}
	if (empty($lottery_num) && !$imsi) {
	//主要是要没有imsi的显示软件，lottery_num没值js会报错
		$lottery_num = 0;
	}
	return $lottery_num;
}

// 设置抽奖次数
function set_lottery_num($lottery_num) {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num, $aid;
	get_lottery_num();
	$redis->set($rkey_imsi_lottery_num, $lottery_num, $r_cache_time);
	$where = array(
		'imsi' => $imsi,
		'aid' => $aid
	);
	$data = array(
		'lottery_num' => $lottery_num,
		'time' => time(),
		'__user_table' => 'imsi_lottery_num' //抽奖机会表
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}

// 抽奖次数减一
function decr_lottery_num() {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num, $aid;
	// 先尝试获得抽奖机会
	$now_num = get_lottery_num();
	$now_num = (int)$now_num;
	if ($now_num <= 0) {
		// 没有抽奖机会，先把缓存还原成0
		$now_num = $redis->set($rkey_imsi_lottery_num, 0);
		return false;
	}
	$now_num = $redis->setx('incr', $rkey_imsi_lottery_num, -1);
	if (!is_int($now_num)) {
		return false;
	}
	// 再修改数据库记录
	$where = array(
		'imsi' => $imsi,
		'aid' => $aid,
	);
	$data = array(
		'lottery_num' => array('exp', '`lottery_num`-1'),
		'time' => time(),
		'__user_table' => 'imsi_lottery_num', //抽奖机会表
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}

// 获取游戏次数
function get_game_num() {
	global $model, $redis, $imsi, $rkey_imsi_game_num, $date, $aid;
	// 从缓存获取已玩游戏次数
	$game_num = $redis->get($rkey_imsi_game_num);
	if (empty($game_num) && $imsi) {
	// 可能没有此用户，或缓存已失效，从数据库获取信息
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $aid,
				'date' => $date
			),
			'table' => 'imsi_game_num' //游戏次数表
		);
		$find = $model->findOne($option, 'lottery/lottery');
		if ($find) {
			$game_num = (int)$find['game_num'];
		} else {
			// 没有记录，新加一条记录
			$imsi_data = array(
				'aid' => $aid,
				'imsi' => $imsi,
				'date' => $date,
				'game_num'=> 0, //今天已玩0次游戏
				'time' => time(),
				'__user_table' => 'imsi_game_num' //游戏次数表
			);
			$model->insert($imsi_data, 'lottery/lottery');
			$game_num = 0;
		}
		// 将用户已玩游戏次数加入缓存
		$redis->set($rkey_imsi_game_num, $game_num, 86400);
	}
	if (empty($game_num) && !$imsi) {
	//主要是要没有imsi的显示软件，lottery_num没值js会报错
		$game_num = 3;
	}
	return $game_num;
}

// 游戏次数加一
function incr_game_num() {
	global $model, $redis, $imsi, $rkey_imsi_game_num, $date, $aid;
	// 尝试获得游戏次数
	$now_num = get_game_num();
	$now_num = (int)$now_num;
	if ($now_num >= 3) {
		// 达到最大游戏次数，先把更新缓存次数
		$now_num = $redis->set($rkey_imsi_game_num, 3);
		return false;
	}
	$now_num = $redis->setx('incr', $rkey_imsi_game_num, 1);
	if (!is_int($now_num)) {
		return false;
	}
	// 再修改数据库记录
	$where = array(
		'imsi' => $imsi,
		'aid' => $aid,
		'date' => $date
	);
	$data = array(
		'game_num' => array('exp', '`game_num`+1'),
		'time' => time(),
		'__user_table' => 'imsi_game_num', //游戏次数表
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}

// 根据获奖等级得到奖品名称
function get_prize_name($award_level)
{
	global $model, $aid;
	$prize_option = array(
		'where' => array(
			'aid' => $aid,
			'status' => 1,
			'level'=> $award_level,
		),
		'cache_time' => '600',
		'table' => 'valentine_draw_prize' //配置奖品表
	);
	$prize_list = $model->findOne($prize_option, 'lottery/lottery');
	return $prize_list['name'];
}