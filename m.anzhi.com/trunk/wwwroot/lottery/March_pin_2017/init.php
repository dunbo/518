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
$model = new GoModel();

// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];

$tplObj -> out['is_share'] = $_GET['is_share'];
// 当前时间
$now = time();
$today = date('Y-m-d');

// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

//$imsi = '460028011771946';  //测试用
$imsi_status = 0;
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
} else {
    $imsi_status = 1;
}
$tplObj->out['sid'] = $sid;
$tplObj->out['imsi_status'] = $imsi_status;

// 加载活动相关信息（活动固定软件）
$fix_package = '["com.hzcf","com.autonavi.minimap","com.iqiyi.qixiu","com.qq.reader"]';

$tplObj->out['stable_soft_lists'] = $fix_package;//软件列表固定的四个软件sqlyo
//$tplObj->out['stable_soft_lists'] = json_encode($fix_package);//软件列表固定的四个软件

$aid = $_GET['aid'];

// redis相关
$date=date("Ymd");
$r_cache_time = '5184000';//redis缓存时间为两个月
$rkey_imsi_puzzle = "March_pin_2017:{$aid}:{$imsi}:".$date;//用户每天拼图次数
$rkey_imsi_lottery_num = "March_pin_2017:{$aid}:{$imsi}:rest_lottery_num";//用户可抽奖次数
$rkey_imsi_package_list = "March_pin_2017:{$aid}:{$imsi}:package_list"; //下载的包
$rkey_imsi_pics_list = "March_pin_2017:{$aid}:pics_list"; //图片  每个活动缓存一下图片就可以，不用每个用户都缓存

$pic_arr = get_pics($aid);
//$random = mt_rand(0,count($out.puzzle_pic)-1);

// 活动id
$tplObj->out['aid'] = $aid;
$tplObj -> out['imgurl'] = getImageHost();
$tplObj->out['puzzle_pic'] = $pic_arr;
//$tplObj->out['puzzle_pic_show'] = $pic_arr[$random];

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
$activity_start_time = $result['start_tm'];//活动开始时间
$activity_end_time = $result['end_tm'];//活动结束时间

// 活动日志
$activity_log_file = "activity_{$aid}.log";

//我的奖品
// 中奖人名单
$award_level_name_arr = array('','一等奖','二等奖','三等奖','四等奖','五等奖','六等奖','七等奖');
$option = array(
	'where' => array(
		'telephone' => array('exp', "!=''"),
		'status' => 1,
		'aid' =>$aid,
	),
	'order' => 'id desc',
	'cache_time' => '600',
	'limit' => '10',
	'table' => 'imsi_lottery_award'
);
$people_award_list = $model->findAll($option, 'lottery/lottery');
foreach ($people_award_list as $key => $row) {
	// 电话号码加密
	$people_award_list[$key]['telephone'] = substr_replace($row['telephone'], '****', 3, 4);
	// 获得中奖内容
	$award_level = $row['award'];
	$prize_name = get_prize_name($award_level);
	$people_award_list[$key]['award_name'] = $prize_name;
	// 中奖时间
	$people_award_list[$key]['date'] = date('Y-m-d', $row['time']);
}

$tplObj->out['people_award_list'] = $people_award_list;

if ($imsi_status) {
	// 初始化用户
	$lottery_num = get_lottery_num();
	$tplObj->out['lottery_num'] = $lottery_num;
}

//记录拼图次数
function set_puzzle_num()
{
	global $model, $redis, $imsi,$rkey_imsi_puzzle;
	$puzzle_num = $redis->setx('incr', $rkey_imsi_puzzle, 1);
	return $puzzle_num;
}
//获取拼图次数
function get_puzzle_num()
{
	global $model, $redis, $imsi,$rkey_imsi_puzzle;
	$puzzle_num = $redis->get($rkey_imsi_puzzle);
	if(empty($puzzle_num) && $puzzle_num !== 0)
	{
		$puzzle_num=0;
	}
	return $puzzle_num;
}

//获取抽奖次数
function get_lottery_num() {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num,$aid;
	// 获得用户的可抽奖次数
	$lottery_num = $redis->get($rkey_imsi_lottery_num);
	if (empty($lottery_num) && $imsi) {
		// 可能没有此用户，或缓存已失效
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $aid,
			),
			'table' => 'imsi_lottery_num',
		);
		$find = $model->findOne($option, 'lottery/lottery');
		if ($find) {
			$lottery_num = (int)$find['lottery_num'];
		} else {
			//没有记录 插入一条记录
			$imsi_data = array(
				'aid' => $aid,
				'imsi' => $imsi,
				'lottery_num'=>0,
				'time' => time(),
				'__user_table' => 'imsi_lottery_num',
			);
			$model -> insert($imsi_data,'lottery/lottery');
			$lottery_num = 0;
		}
		$redis->set($rkey_imsi_lottery_num,$lottery_num,$r_cache_time);
	}
	if(empty($lottery_num)&&!$imsi)//主要是要没有imsi的显示软件，lottery_num没值js会报错
	{
		$lottery_num = 0;
	}
	return $lottery_num;
}

function set_lottery_num($lottery_num) {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num,$aid;
	get_lottery_num();
	$redis->set($rkey_imsi_lottery_num, $lottery_num, $r_cache_time);
	$where = array(
		'imsi' => $imsi,
		'aid' => $aid,
	);
	$data = array(
		'lottery_num' => $lottery_num,
		'time' => time(),
		'__user_table' => 'imsi_lottery_num',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}

function reduce_lottery_num_by_1() {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num,$aid;
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
		'aid' =>$aid,
	);
	$data = array(
		'lottery_num' => array('exp', '`lottery_num`-1'),
		'time' => time(),
		'__user_table' => 'imsi_lottery_num',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}

//获取拼图图片
function get_pics($aid){
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_pics_list,$now;	
	$puzzle_pics = $redis->get($rkey_imsi_pics_list);	
	if($puzzle_pics === null){	
		//加载拼图图片  sj_rio_olympic_pics
		$activity_option = array(
			'where' => array(
				'status' => 1,
				'start_tm' =>array('exp', "<=".$now),
				'end_tm' =>array('exp', ">=".$now),
			),
			'cache_time' => 600,
			'table' => 'sj_rio_olympic_pics',
		);
		$puzzle_result = $model -> findOne($activity_option);
		$puzzle_pics = array_values(json_decode($puzzle_result['pics'],true));
		
		$redis->set($rkey_imsi_pics_list,$puzzle_pics,60*60);//设置图片缓存1小时
	}
	return $puzzle_pics;			
}

//根据获奖等级得到奖品名称
function get_prize_name($award_level)
{
	global $model,$aid;
	$prize_option = array(
		'where' => array(
			'aid' => $aid,
			'status' => 1,
			'level'=>$award_level,
		),
		'cache_time' => '600',
		'table' => 'valentine_draw_prize'
	);
	$prize_list = $model->findOne($prize_option, 'lottery/lottery');
	return $prize_list['name'];
}