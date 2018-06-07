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
$tplObj -> out['new_static_url'] = $configs['new_static_url'];

// 当前时间
$now = time();

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


$fix_package = '["com.undao.traveltesti","com.ecc.ka","com.nuomi","com.zimadai"]';
//$fix_package = '["com.undao.traveltesti","com.ecc.ka"]';
$tplObj->out['stable_soft_lists'] = $fix_package;//软件列表固定的四个软件
$aid = $_GET['aid'];

// redis相关
$date=date("Ymd");
$r_cache_time = '5184000';//redis缓存时间为两个月
$rkey_imsi_grasp = "little_elf:{$aid}:{$imsi}:".$date;//用户每天抓取精灵次数
$rkey_imsi_lottery_num = "little_elf:{$aid}:{$imsi}:rest_lottery_num";//用户可抽奖次数
$rkey_imsi_package_list = "little_elf:{$aid}:{$imsi}:package_list"; //下载的包
//$rkey_imsi_pics_list = "little_elf:{$aid}:{$imsi}:pics_list"; //图片

//$pic_arr = get_pics($aid);

// 活动id
$tplObj->out['aid'] = $aid;
$tplObj -> out['imgurl'] = getImageHost();


// 根据活动id获得软件页面id（主要用在活动页面下载软件，判断下载的软件是否有效）
$activity_option = array(
    'where' => array(
        'id' => $aid,
        'status' => 1,
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
		'aid'=>$aid,
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
	$grasp_num = get_grasp_num();
	$tplObj->out['lottery_num'] = $lottery_num;
	$tplObj->out['grasp_num'] = $grasp_num;
}


//记录抓取次数
function set_grasp_num()
{
	global $model, $redis, $imsi,$rkey_imsi_grasp;
	$grasp_num = $redis->setx('incr', $rkey_imsi_grasp, 1);
	return $grasp_num;
}
//获取点击活动次数
function get_grasp_num()
{
	global $model, $redis, $imsi,$rkey_imsi_grasp;
	$grasp_num = $redis->get($rkey_imsi_grasp);
	if(empty($grasp_num) && $grasp_num !== 0)
	{
		$grasp_num=0;
	}
	return $grasp_num;
}
//获取抽奖次数
function get_lottery_num() {
	global $model, $redis, $imsi, $r_cache_time, $rkey_imsi_lottery_num, $aid;
	// 获得用户的可抽奖次数
	$lottery_num = $redis->get($rkey_imsi_lottery_num);
	if (empty($lottery_num) && $imsi) {
		// 可能没有此用户，或缓存已失效
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' =>$aid,
			),
			'table' => 'imsi_lottery_num',
		);
		$find = $model->findOne($option, 'lottery/lottery');
		if ($find) {
			$lottery_num = (int)$find['lottery_num'];
		} else {
			//没有记录 插入一条记录
			$imsi_data = array(
				'imsi' => $imsi,
				'lottery_num' => 0,
				'aid' => $aid,
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
		'aid' =>$aid,
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
		'aid' => $aid,
	);
	$data = array(
		'lottery_num' => array('exp', '`lottery_num`-1'),
		'time' => time(),
		'__user_table' => 'imsi_lottery_num',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
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